<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\CardTemplate;
use App\Models\NfcCard;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\AdminBulkOrderNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;

class EmployeeController extends Controller
{
    /**
     * Resolve the Business account (owner) the current user operates within.
     * Returns null when the user does not belong to a Business tenant.
     */
    private function resolveBusinessAccount(Request $request): ?User
    {
        $user = $request->user();

        return $user ? $user->resolvedBusinessAccount() : null;
    }

    /**
     * Upload employee data (CSV/XLSX) and create bulk NFC card orders.
     *
     * Cards are created against the Business account and automatically assigned
     * to the matching employee account when one already exists under this
     * business (matched by e-mail). When no account exists yet the card is held
     * by the owner until an admin creates the employee account, after which it
     * can be reassigned via POST /business/cards/bulk-assign.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadEmployees(Request $request)
    {
        $businessAccount = $this->resolveBusinessAccount($request);

        if (!$businessAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their delegated managers can upload employee data'
            ], 403);
        }

        // Validate request
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx|max:5120', // 5MB max
            'design_method' => 'required|in:template,custom',
            'selected_template' => 'required_if:design_method,template',
            'front_design' => 'required_if:design_method,custom|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'back_design' => 'required_if:design_method,custom|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $employees = $this->parseEmployeeFile($file);

            if (empty($employees)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No valid employee data found in file'
                ], 400);
            }

            // Check quota
            $quotaInfo = $businessAccount->getQuotaInfo();
            $requiredSlots = count($employees);

            if ($quotaInfo['available_card_quota'] < $requiredSlots) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient quota. You need {$requiredSlots} cards but only have {$quotaInfo['available_card_quota']} available.",
                    'data' => [
                        'required' => $requiredSlots,
                        'available' => $quotaInfo['available_card_quota']
                    ]
                ], 403);
            }

            // Store file for admin review
            $storedFilePath = $file->store('employee-uploads', 'private');

            // Resolve design assets once — they apply to every card in the batch.
            $design = $this->resolveDesignPayload($request);

            $createdCards = [];
            $errors = [];

            foreach ($employees as $index => $employeeData) {
                try {
                    // Match an existing employee account under this business by e-mail.
                    $employee = User::where('parent_business_id', $businessAccount->id)
                        ->whereRaw('LOWER(email) = ?', [strtolower($employeeData['email'])])
                        ->first();

                    $card = NfcCard::create([
                        // Assigned to the employee when they already have an account,
                        // otherwise held by the business owner until reassigned.
                        'user_id' => $employee?->id ?? $businessAccount->id,
                        'business_account_id' => $businessAccount->id,
                        'card_owner' => $employeeData['name'],
                        'contact_number' => $employeeData['contact_number'] ?? null,
                        'billing_address' => $employeeData['address'] ?: ($employeeData['delivery_address'] ?? ''),
                        'shipping_address' => $employeeData['delivery_address'] ?? $employeeData['address'],
                        'purchase_date' => now(),
                        'subscription_plan' => 'business',
                        'purchase_amount' => 0,
                        'payment_method' => 'business_plan',
                        'card_template_id' => $design['card_template_id'],
                        'is_custom_design' => $design['is_custom_design'],
                        'custom_design_front_url' => $design['front_url'],
                        'custom_design_back_url' => $design['back_url'],
                        'design_notes' => $design['design_notes'],
                        'notes' => $this->buildEmployeeNotes($employeeData),
                        // Business plan cards are covered by the tenant quota, so they
                        // enter the fulfilment pipeline already payment-verified.
                        'status' => 'payment_verified',
                    ]);

                    $createdCards[] = [
                        'id' => $card->id,
                        'name' => $card->card_owner,
                        'email' => $employeeData['email'],
                        'position' => $employeeData['position'],
                        'assigned_to_employee' => $employee?->id,
                        'assigned' => $employee !== null,
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'employee' => $employeeData['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }

            if (empty($createdCards) && !empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No card orders could be created.',
                    'data' => ['errors' => $errors],
                ], 422);
            }

            // Send notification to Super Admin
            $this->notifySuperAdmin($businessAccount, $storedFilePath, $createdCards, $request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdCards) . ' employee card orders created successfully. Super Admin has been notified.',
                'data' => [
                    'created_count' => count($createdCards),
                    'assigned_count' => collect($createdCards)->where('assigned', true)->count(),
                    'error_count' => count($errors),
                    'cards' => $createdCards,
                    'errors' => $errors,
                    'remaining_quota' => $quotaInfo['available_card_quota'] - count($createdCards)
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to process employee upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve the design payload shared by all cards in a bulk upload.
     */
    private function resolveDesignPayload(Request $request): array
    {
        $isCustom = $request->design_method === 'custom';
        $frontUrl = null;
        $backUrl = null;
        $templateId = null;
        $designNotes = null;

        if ($isCustom) {
            if ($request->hasFile('front_design')) {
                $path = $request->file('front_design')->store('card-designs', 'public');
                $frontUrl = Storage::url($path);
            }
            if ($request->hasFile('back_design')) {
                $path = $request->file('back_design')->store('card-designs', 'public');
                $backUrl = Storage::url($path);
            }
        } else {
            $selected = $request->selected_template;
            if (is_numeric($selected) && CardTemplate::whereKey((int) $selected)->exists()) {
                $templateId = (int) $selected;
            } else {
                $designNotes = 'Template: ' . (string) $selected;
            }
        }

        return [
            'is_custom_design' => $isCustom,
            'front_url' => $frontUrl,
            'back_url' => $backUrl,
            'card_template_id' => $templateId,
            'design_notes' => $designNotes,
        ];
    }

    /**
     * Compose the free-text notes that preserve the extra CSV columns
     * (position / e-mail / website) that have no dedicated column.
     */
    private function buildEmployeeNotes(array $employeeData): string
    {
        $parts = array_filter([
            !empty($employeeData['position']) ? 'Position: ' . $employeeData['position'] : null,
            !empty($employeeData['email']) ? 'Employee e-mail: ' . $employeeData['email'] : null,
            !empty($employeeData['website']) ? 'Website: ' . $employeeData['website'] : null,
        ]);

        return implode(' | ', $parts);
    }

    /**
     * Parse CSV/XLSX file to extract employee data
     *
     * @param $file
     * @return array
     */
    private function parseEmployeeFile($file)
    {
        $employees = [];
        $extension = $file->getClientOriginalExtension();

        try {
            if ($extension === 'csv') {
                // Parse CSV
                $csvData = file_get_contents($file->getRealPath());
                $lines = explode("\n", $csvData);

                if (count($lines) < 2) {
                    return [];
                }

                // Parse header
                $headers = str_getcsv($lines[0]);
                $headers = array_map(function ($header) {
                    return strtolower(trim($header));
                }, $headers);

                // Parse rows
                for ($i = 1; $i < count($lines); $i++) {
                    if (empty(trim($lines[$i]))) {
                        continue;
                    }

                    $row = str_getcsv($lines[$i]);
                    $employee = $this->mapRow($headers, $row);

                    if (!empty($employee['name']) && !empty($employee['email']) && !empty($employee['position'])) {
                        $employees[] = $employee;
                    }
                }
            } elseif ($extension === 'xlsx') {
                // Parse XLSX using Maatwebsite/Excel
                $data = Excel::toArray(new EmployeeImport, $file);

                if (empty($data) || empty($data[0])) {
                    return [];
                }

                $rows = $data[0];
                if (count($rows) < 2) {
                    return [];
                }

                $headers = array_map(function ($header) {
                    return strtolower(trim($header));
                }, $rows[0]);

                for ($i = 1; $i < count($rows); $i++) {
                    $employee = $this->mapRow($headers, $rows[$i]);

                    if (!empty($employee['name']) && !empty($employee['email']) && !empty($employee['position'])) {
                        $employees[] = $employee;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error parsing employee file: ' . $e->getMessage());
            return [];
        }

        return $employees;
    }

    /**
     * Map a raw header/row pair to the normalised employee array.
     */
    private function mapRow(array $headers, array $row): array
    {
        $fieldMap = [
            'name' => 'name',
            'email' => 'email',
            'position' => 'position',
            'contact number' => 'contact_number',
            'contact_number' => 'contact_number',
            'website' => 'website',
            'address' => 'address',
            'delivery address' => 'delivery_address',
            'delivery_address' => 'delivery_address',
        ];

        $employee = [];
        foreach ($headers as $index => $header) {
            $value = isset($row[$index]) ? trim((string) $row[$index]) : '';
            $mappedField = $fieldMap[$header] ?? null;
            if ($mappedField) {
                $employee[$mappedField] = $value;
            }
        }

        // Default blank-string fields so the DB write never receives nulls.
        $employee += [
            'name' => '',
            'email' => '',
            'position' => '',
            'contact_number' => '',
            'website' => '',
            'address' => '',
            'delivery_address' => '',
        ];

        if (empty($employee['delivery_address']) && !empty($employee['address'])) {
            $employee['delivery_address'] = $employee['address'];
        }

        return $employee;
    }

    /**
     * Send notification to Super Admin about bulk order
     *
     * @param User $businessAccount
     * @param string $filePath
     * @param array $cards
     * @param Request $request
     * @return void
     */
    private function notifySuperAdmin($businessAccount, $filePath, $cards, $request)
    {
        // Get all super admin users
        $superAdmins = User::where('is_admin', true)
            ->where('subscription_plan', 'super-admin')
            ->get();

        $notificationData = [
            'type' => 'business_bulk_order_placed',
            'priority' => 'high',
            'pinned' => true,
            'business_account' => [
                'id' => $businessAccount->id,
                'name' => $businessAccount->full_name,
                'company' => $businessAccount->company,
                'email' => $businessAccount->email,
            ],
            'order_details' => [
                'employee_count' => count($cards),
                'design_method' => $request->design_method,
                'selected_template' => $request->selected_template,
                'order_date' => now()->toDateTimeString(),
            ],
            'file_path' => $filePath,
            'cards' => $cards,
        ];

        foreach ($superAdmins as $admin) {
            $admin->notify(new AdminBulkOrderNotification($notificationData));
        }
    }

    /**
     * Bulk-assign existing Business cards to an employee (or release them back
     * to the account owner).
     *
     * Body:
     *   card_ids    : int[]   (required) cards to reassign
     *   employee_id : int|null (employee under this business; null = owner/unassign)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkAssignCards(Request $request)
    {
        $businessAccount = $this->resolveBusinessAccount($request);

        if (!$businessAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their delegated managers can assign cards'
            ], 403);
        }

        $validated = $request->validate([
            'card_ids' => 'required|array|min:1',
            'card_ids.*' => 'integer',
            'employee_id' => 'nullable|integer',
        ]);

        // Validate the target employee belongs to this business.
        $employee = null;
        if (!empty($validated['employee_id'])) {
            $employee = User::where('id', $validated['employee_id'])
                ->where('parent_business_id', $businessAccount->id)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found or does not belong to your account'
                ], 404);
            }
        }

        // Only cards that belong to this Business account may be reassigned.
        $cards = NfcCard::whereIn('id', $validated['card_ids'])
            ->where('business_account_id', $businessAccount->id)
            ->get();

        if ($cards->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No matching cards found for this Business account'
            ], 404);
        }

        $targetUserId = $employee?->id ?? $businessAccount->id;
        $updated = [];

        DB::transaction(function () use ($cards, $targetUserId, &$updated) {
            foreach ($cards as $card) {
                $card->user_id = $targetUserId;
                $card->save();

                $updated[] = [
                    'id' => $card->id,
                    'card_owner' => $card->card_owner,
                    'user_id' => $card->user_id,
                ];
            }
        });

        return response()->json([
            'success' => true,
            'message' => $employee
                ? count($updated) . ' card(s) assigned to ' . $employee->full_name
                : count($updated) . ' card(s) returned to the account owner',
            'data' => [
                'assigned_count' => count($updated),
                'assigned_to' => $employee ? [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'email' => $employee->email,
                ] : null,
                'cards' => $updated,
            ]
        ]);
    }

    /**
     * Update an employee's delegated Business role (owner-only action).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRole(Request $request, $id)
    {
        $user = $request->user();

        // Only the Business account owner may change roles (not delegated admins).
        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the Business account owner can change employee roles'
            ], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', User::BUSINESS_EMPLOYEE_ROLES),
        ]);

        $employee = User::where('id', $id)
            ->where('parent_business_id', $user->id)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or does not belong to your account'
            ], 404);
        }

        $employee->business_role = $validated['role'];
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee role updated successfully',
            'data' => [
                'id' => $employee->id,
                'business_role' => $employee->business_role,
            ]
        ]);
    }

    /**
     * Reset employee password
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, $id)
    {
        $businessAccount = $this->resolveBusinessAccount($request);

        if (!$businessAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their delegated managers can reset employee passwords'
            ], 403);
        }

        // Find employee
        $employee = User::where('id', $id)
            ->where('parent_business_id', $businessAccount->id)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or does not belong to your account'
            ], 404);
        }

        // Issue a one-time password-reset token and email the employee a link to
        // set their own password. We deliberately do NOT email a plaintext
        // password, and we do not return one in the API response.
        $rawToken = bin2hex(random_bytes(48));
        $tokenHash = hash_hmac('sha256', $rawToken, config('app.key'));

        // Invalidate any existing unused reset tokens for this employee.
        PasswordReset::invalidateUserTokens($employee->id);

        PasswordReset::create([
            'user_id' => $employee->id,
            'token_hash' => $tokenHash,
            'used' => false,
            'expires_at' => Carbon::now()->addHour(),
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            Mail::to($employee->email)->send(
                new PasswordResetMail($employee, $rawToken, $request->ip())
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send employee password reset email', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'A password reset link has been sent to the employee\'s email address.',
            'data' => [
                'employee_email' => $employee->email,
            ]
        ]);
    }

    /**
     * Get employee details
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeDetails(Request $request, $id)
    {
        $businessAccount = $this->resolveBusinessAccount($request);

        if (!$businessAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their delegated managers can view employee details'
            ], 403);
        }

        // Find employee with NFC card
        $employee = User::where('id', $id)
            ->where('parent_business_id', $businessAccount->id)
            ->with(['nfcCards' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or does not belong to your account'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'job_title' => $employee->job_title,
                    'company' => $employee->company,
                    'business_role' => $employee->businessRole(),
                    'subscription_active' => $employee->subscription_active,
                    'created_at' => $employee->created_at,
                ],
                'nfc_cards' => $employee->nfcCards->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'card_owner' => $card->card_owner,
                        'status' => $card->status,
                        'purchase_date' => $card->purchase_date,
                        'delivered_date' => $card->delivered_date,
                    ];
                })
            ]
        ]);
    }

    /**
     * Toggle employee account status (activate/deactivate)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request, $id)
    {
        $businessAccount = $this->resolveBusinessAccount($request);

        if (!$businessAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their delegated managers can toggle employee status'
            ], 403);
        }

        // Find employee
        $employee = User::where('id', $id)
            ->where('parent_business_id', $businessAccount->id)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or does not belong to your account'
            ], 404);
        }

        // Validate request
        $request->validate([
            'subscription_active' => 'required|boolean'
        ]);

        // Update status
        $employee->subscription_active = $request->subscription_active;
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Employee status updated successfully',
            'data' => [
                'id' => $employee->id,
                'subscription_active' => $employee->subscription_active
            ]
        ]);
    }
}
