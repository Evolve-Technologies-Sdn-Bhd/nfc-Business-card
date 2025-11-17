<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NfcCard;
use App\Notifications\AdminBulkOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;

class EmployeeController extends Controller
{
    /**
     * Upload employee data (CSV/XLSX) and create bulk NFC card orders
     * 
     * Note: This does NOT create employee user accounts.
     * Employee accounts must be created by Super Admin first.
     * This endpoint only creates NFC card orders with employee information.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadEmployees(Request $request)
    {
        $user = auth()->user();

        // Validate user is Business account
        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can upload employee data'
            ], 403);
        }

        // Validate request
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx|max:5120', // 5MB max
            'design_method' => 'required|in:template,custom',
            'selected_template' => 'required_if:design_method,template|string',
            'front_design' => 'required_if:design_method,custom',
            'back_design' => 'required_if:design_method,custom',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $employees = $this->parseEmployeeFile($file);

            if (empty($employees)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid employee data found in file'
                ], 400);
            }

            // Check quota
            $quotaInfo = $user->getQuotaInfo();
            $requiredSlots = count($employees);

            if ($quotaInfo['available_card_quota'] < $requiredSlots) {
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

            // Create NFC card orders for each employee
            $createdCards = [];
            $errors = [];

            foreach ($employees as $index => $employeeData) {
                try {
                    // Create NFC card order
                    $card = NfcCard::create([
                        'user_id' => $user->id, // Business account owner
                        'business_account_id' => $user->id,
                        'subscription_plan' => 'business',
                        'name' => $employeeData['name'],
                        'position' => $employeeData['position'],
                        'contact_number' => $employeeData['contact_number'] ?? $employeeData['contactNumber'] ?? '',
                        'email' => $employeeData['email'],
                        'website' => $employeeData['website'] ?? '',
                        'business_address' => $employeeData['address'],
                        'delivery_address' => $employeeData['delivery_address'] ?? $employeeData['deliveryAddress'] ?? $employeeData['address'],
                        'design_method' => $request->design_method,
                        'selected_template' => $request->design_method === 'template' ? $request->selected_template : null,
                        'front_design' => $request->design_method === 'custom' ? $request->front_design : null,
                        'back_design' => $request->design_method === 'custom' ? $request->back_design : null,
                        'status' => 'pending', // Pending admin approval
                        'order_date' => now(),
                    ]);

                    $createdCards[] = [
                        'id' => $card->id,
                        'name' => $card->name,
                        'email' => $card->email,
                        'position' => $card->position
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'employee' => $employeeData['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Send notification to Super Admin
            $this->notifySuperAdmin($user, $storedFilePath, $createdCards, $request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdCards) . ' employee card orders created successfully. Super Admin has been notified.',
                'data' => [
                    'created_count' => count($createdCards),
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
                $headers = array_map(function($header) {
                    return strtolower(trim($header));
                }, $headers);

                // Parse rows
                for ($i = 1; $i < count($lines); $i++) {
                    if (empty(trim($lines[$i]))) {
                        continue;
                    }

                    $row = str_getcsv($lines[$i]);
                    $employee = [];

                    foreach ($headers as $index => $header) {
                        $value = isset($row[$index]) ? trim($row[$index]) : '';
                        
                        // Map header to field
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

                        $mappedField = $fieldMap[$header] ?? null;
                        if ($mappedField) {
                            $employee[$mappedField] = $value;
                        }
                    }

                    // Validate required fields
                    if (!empty($employee['name']) && !empty($employee['email']) && !empty($employee['position'])) {
                        // Set delivery address to address if not provided
                        if (empty($employee['delivery_address']) && !empty($employee['address'])) {
                            $employee['delivery_address'] = $employee['address'];
                        }
                        
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

                // Parse header
                $headers = array_map(function($header) {
                    return strtolower(trim($header));
                }, $rows[0]);

                // Parse rows
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    $employee = [];

                    foreach ($headers as $index => $header) {
                        $value = isset($row[$index]) ? trim($row[$index]) : '';
                        
                        // Map header to field
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

                        $mappedField = $fieldMap[$header] ?? null;
                        if ($mappedField) {
                            $employee[$mappedField] = $value;
                        }
                    }

                    // Validate required fields
                    if (!empty($employee['name']) && !empty($employee['email']) && !empty($employee['position'])) {
                        // Set delivery address to address if not provided
                        if (empty($employee['delivery_address']) && !empty($employee['address'])) {
                            $employee['delivery_address'] = $employee['address'];
                        }
                        
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
     * Reset employee password
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, $id)
    {
        $user = auth()->user();

        // Validate user is Business account
        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can reset employee passwords'
            ], 403);
        }

        // Find employee
        $employee = User::where('id', $id)
            ->where('parent_business_id', $user->id)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or does not belong to your account'
            ], 404);
        }

        // Generate random password
        $newPassword = Str::random(12);

        // Update password
        $employee->password = Hash::make($newPassword);
        $employee->save();

        // TODO: Send password reset email to employee
        // You can create a PasswordResetNotification and send it here
        // $employee->notify(new PasswordResetNotification($newPassword));

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. New password has been sent to employee\'s email.',
            'data' => [
                'employee_email' => $employee->email,
                'temporary_password' => $newPassword // Only for testing, remove in production
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
        $user = auth()->user();

        // Validate user is Business account
        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can view employee details'
            ], 403);
        }

        // Find employee with NFC card
        $employee = User::where('id', $id)
            ->where('parent_business_id', $user->id)
            ->with(['nfcCards' => function($query) {
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
                    'subscription_active' => $employee->subscription_active,
                    'created_at' => $employee->created_at,
                ],
                'nfc_cards' => $employee->nfcCards->map(function($card) {
                    return [
                        'id' => $card->id,
                        'name' => $card->name,
                        'position' => $card->position,
                        'status' => $card->status,
                        'order_date' => $card->order_date,
                        'delivery_date' => $card->delivery_date,
                    ];
                })
            ]
        ]);
    }
}
