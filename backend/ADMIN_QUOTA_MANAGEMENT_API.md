# Admin Quota Management - Backend API Implementation

## Overview

Admin can set and update Business account quota (`total_card_quota` and `total_account_slots`) when creating or editing users. These values must be stored in the database and validated properly.

---

## Database Schema

### users Table

**Required Columns**:

```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS total_card_quota INT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS total_account_slots INT DEFAULT 0;
```

**Column Descriptions**:

-   `total_card_quota`: Total number of Business Plan NFC cards that can be ordered (admin + all employees)
-   `total_account_slots`: Total number of employee accounts that can be created under this Business account

**Important**: These fields are **only used for Business Plan accounts** (`subscription_plan = 'business'` AND `parent_business_id IS NULL`)

---

## API Endpoints

### 1. POST /api/admin/users

**Purpose**: Admin creates a new user

**Request Body**:

```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@company.com",
    "phone": "+60 12-345 6789",
    "company": "ABC Corporation",
    "job_title": "CEO",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "subscription_plan": "business",
    "is_admin": false,
    "admin_role": null,
    "admin_permissions": [],
    "total_account_slots": 50,
    "total_card_quota": 50
}
```

**Backend Implementation** (`app/Http/Controllers/Admin/UserController.php`):

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'subscription_plan' => 'required|in:free,basic,premium,business',
            'is_admin' => 'boolean',
            'admin_role' => 'nullable|string|in:admin,moderator',
            'admin_permissions' => 'array',

            // Business Plan specific fields
            'total_account_slots' => 'nullable|integer|min:0',
            'total_card_quota' => 'nullable|integer|min:0',
        ]);

        // Create user
        $user = new User();
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->full_name = $validated['first_name'] . ' ' . $validated['last_name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->company = $validated['company'];
        $user->job_title = $validated['job_title'];
        $user->password = Hash::make($validated['password']);
        $user->subscription_plan = $validated['subscription_plan'];
        $user->subscription_active = true;
        $user->is_admin = $validated['is_admin'] ?? false;
        $user->admin_role = $validated['admin_role'];
        $user->admin_permissions = $validated['admin_permissions'] ?? [];

        // Set Business Plan quota fields (only if Business Plan)
        if ($validated['subscription_plan'] === 'business') {
            $user->total_account_slots = $validated['total_account_slots'] ?? 10;
            $user->total_card_quota = $validated['total_card_quota'] ?? 10;
        } else {
            // Non-Business plans should have 0
            $user->total_account_slots = 0;
            $user->total_card_quota = 0;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }
}
```

---

### 2. PUT /api/admin/users/{id}

**Purpose**: Admin updates an existing user

**Request Body**:

```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@company.com",
    "phone": "+60 12-345 6789",
    "company": "ABC Corporation",
    "job_title": "CEO",
    "subscription_plan": "business",
    "is_admin": false,
    "admin_role": null,
    "admin_permissions": [],
    "total_account_slots": 100,
    "total_card_quota": 100
}
```

**Backend Implementation**:

```php
public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    // Validate request
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($user->id)
        ],
        'phone' => 'nullable|string|max:50',
        'company' => 'nullable|string|max:255',
        'job_title' => 'nullable|string|max:255',
        'password' => 'nullable|string|min:8|confirmed',
        'subscription_plan' => 'required|in:free,basic,premium,business',
        'is_admin' => 'boolean',
        'admin_role' => 'nullable|string|in:admin,moderator',
        'admin_permissions' => 'array',

        // Business Plan specific fields
        'total_account_slots' => 'nullable|integer|min:0',
        'total_card_quota' => 'nullable|integer|min:0',
    ]);

    // Update basic fields
    $user->first_name = $validated['first_name'];
    $user->last_name = $validated['last_name'];
    $user->full_name = $validated['first_name'] . ' ' . $validated['last_name'];
    $user->email = $validated['email'];
    $user->phone = $validated['phone'];
    $user->company = $validated['company'];
    $user->job_title = $validated['job_title'];

    // Update password only if provided
    if (!empty($validated['password'])) {
        $user->password = Hash::make($validated['password']);
    }

    $user->subscription_plan = $validated['subscription_plan'];
    $user->is_admin = $validated['is_admin'] ?? false;
    $user->admin_role = $validated['admin_role'];
    $user->admin_permissions = $validated['admin_permissions'] ?? [];

    // Update Business Plan quota fields
    if ($validated['subscription_plan'] === 'business') {
        // Get current usage to validate minimum values
        $orderedCardsCount = \App\Models\NfcCard::where('business_account_id', $user->id)
            ->where('subscription_plan', 'business')
            ->count();

        $employeesCount = User::where('parent_business_id', $user->id)->count();

        // Validate new quota values
        $newCardQuota = $validated['total_card_quota'] ?? 10;
        $newAccountSlots = $validated['total_account_slots'] ?? 10;

        // Check if new quota is sufficient for current usage
        if ($newCardQuota < $orderedCardsCount) {
            return response()->json([
                'success' => false,
                'message' => "Cannot set card quota to {$newCardQuota}. Already ordered: {$orderedCardsCount} cards.",
                'errors' => [
                    'total_card_quota' => ["Minimum value is {$orderedCardsCount} (already ordered cards)"]
                ]
            ], 422);
        }

        if ($newAccountSlots < $employeesCount) {
            return response()->json([
                'success' => false,
                'message' => "Cannot set account slots to {$newAccountSlots}. Current employees: {$employeesCount}.",
                'errors' => [
                    'total_account_slots' => ["Minimum value is {$employeesCount} (current employees)"]
                ]
            ], 422);
        }

        $user->total_account_slots = $newAccountSlots;
        $user->total_card_quota = $newCardQuota;
    } else {
        // If changing from Business to another plan, clear quota
        $user->total_account_slots = 0;
        $user->total_card_quota = 0;
    }

    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'User updated successfully',
        'data' => $user
    ]);
}
```

---

### 3. GET /api/admin/users

**Purpose**: Get list of all users (with quota info for Business accounts)

**Backend Implementation**:

```php
public function index(Request $request)
{
    $query = User::query();

    // Apply filters
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('full_name', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('subscription_plan')) {
        $query->where('subscription_plan', $request->subscription_plan);
    }

    if ($request->filled('is_admin')) {
        $isAdmin = $request->is_admin === 'true';
        $query->where('is_admin', $isAdmin);
    }

    // Paginate results
    $users = $query->orderBy('created_at', 'desc')->paginate(20);

    // Add quota information for Business accounts
    $users->getCollection()->transform(function ($user) {
        if ($user->subscription_plan === 'business' && !$user->parent_business_id) {
            // Count actual card orders
            $user->ordered_cards_count = \App\Models\NfcCard::where('business_account_id', $user->id)
                ->where('subscription_plan', 'business')
                ->count();

            // Count employees
            $user->employees_count = User::where('parent_business_id', $user->id)->count();
        }
        return $user;
    });

    return response()->json([
        'success' => true,
        'data' => $users->items(),
        'pagination' => [
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'total_pages' => $users->lastPage(),
        ]
    ]);
}
```

---

## Validation Rules Summary

### Creating Business User

```php
'total_account_slots' => 'nullable|integer|min:0'  // Default: 10
'total_card_quota' => 'nullable|integer|min:0'      // Default: 10
```

### Updating Business User

```php
'total_account_slots' => [
    'nullable',
    'integer',
    'min:' . $employeesCount  // Cannot be less than current employees
]

'total_card_quota' => [
    'nullable',
    'integer',
    'min:' . $orderedCardsCount  // Cannot be less than already ordered cards
]
```

---

## Business Logic Rules

### 1. Creating New User

-   If `subscription_plan === 'business'`:
    -   Set `total_account_slots` (default: 10)
    -   Set `total_card_quota` (default: 10)
-   If other plans:
    -   Set both fields to `0`

### 2. Updating Existing User

-   If changing TO Business Plan:
    -   Set quota values from request or defaults
-   If changing FROM Business Plan to other:
    -   Clear quota (set to 0)
    -   Validate no active employees exist
    -   Validate no cards ordered
-   If staying Business Plan:
    -   Validate new quota >= current usage
    -   Update values

### 3. Quota Validation

**Card Quota**:

```php
$orderedCardsCount = NfcCard::where('business_account_id', $userId)
    ->where('subscription_plan', 'business')
    ->count();

if ($newCardQuota < $orderedCardsCount) {
    // Error: Cannot reduce below current usage
}
```

**Account Slots**:

```php
$employeesCount = User::where('parent_business_id', $userId)->count();

if ($newAccountSlots < $employeesCount) {
    // Error: Cannot reduce below current employees
}
```

---

## Migration File

**Location**: `database/migrations/xxxx_xx_xx_add_quota_fields_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_account_slots')->default(0)->after('subscription_plan');
            $table->integer('total_card_quota')->default(0)->after('total_account_slots');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_account_slots', 'total_card_quota']);
        });
    }
};
```

**Run Migration**:

```bash
php artisan migrate
```

---

## Routes

**Location**: `routes/api.php`

```php
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
});
```

---

## Error Handling

### Example Error Responses

**1. Card Quota Too Low**:

```json
{
    "success": false,
    "message": "Cannot set card quota to 5. Already ordered: 8 cards.",
    "errors": {
        "total_card_quota": ["Minimum value is 8 (already ordered cards)"]
    }
}
```

**2. Account Slots Too Low**:

```json
{
    "success": false,
    "message": "Cannot set account slots to 3. Current employees: 5.",
    "errors": {
        "total_account_slots": ["Minimum value is 5 (current employees)"]
    }
}
```

---

## Testing Checklist

### Backend Tests

-   [ ] Create Business user with quota fields
-   [ ] Create non-Business user (quota should be 0)
-   [ ] Update Business user quota (increase)
-   [ ] Update Business user quota (decrease - valid)
-   [ ] Update Business user quota (decrease - invalid, below usage)
-   [ ] Change user from Business to Free (validate constraints)
-   [ ] Change user from Free to Business (set default quota)
-   [ ] GET /api/admin/users returns quota info for Business users
-   [ ] GET /api/admin/users returns 0 for non-Business users

### Database Tests

-   [ ] Quota fields exist in users table
-   [ ] Default values are 0
-   [ ] Fields accept integer values
-   [ ] Fields can be null (handled by default 0)

---

## Summary

**Key Points**:

1. **Database**: Add `total_account_slots` and `total_card_quota` columns to `users` table
2. **Create User**: Set quota for Business Plan, 0 for others
3. **Update User**: Validate quota >= current usage before updating
4. **Get Users**: Return quota info and usage counts for Business accounts
5. **Validation**: Prevent reducing quota below current usage

**Formula**:

-   Used Card Quota = `COUNT(nfc_cards WHERE business_account_id = X AND subscription_plan = 'business')`
-   Used Account Slots = `COUNT(users WHERE parent_business_id = X)`
-   Available Card Quota = `total_card_quota - ordered_cards_count`
-   Available Account Slots = `total_account_slots - employees_count`

---

# Business User Quota APIs

## Overview

Business account users (and their employees) need APIs to view their quota information when managing cards and employees. These APIs are used in:

-   **BusinessCardManagement.vue** - Display card quota
-   **BusinessEmployeeManagement.vue** - Display employee quota

---

## API Endpoints for Business Users

### 1. GET /api/business/card-quota

**Purpose**: Get card quota information for the current Business account

**Authentication**: Required (Sanctum)

**Authorization**:

-   Business account (subscription_plan = 'business' AND parent_business_id IS NULL)
-   OR Employee under Business account (parent_business_id IS NOT NULL)

**Request**: No body required

**Response**:

```json
{
    "success": true,
    "data": {
        "total_card_quota": 50,
        "ordered_cards_count": 12,
        "available_card_quota": 38,
        "business_account_id": 123,
        "account_info": {
            "total_account_slots": 50,
            "employees_count": 8,
            "available_account_slots": 42
        }
    }
}
```

**Backend Implementation**:

```php
<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NfcCard;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
    public function getCardQuota(Request $request)
    {
        $user = auth()->user();

        // Determine if user is Business account or employee
        if ($user->subscription_plan === 'business' && !$user->parent_business_id) {
            // User is Business account owner
            $businessAccountId = $user->id;
            $totalCardQuota = $user->total_card_quota ?? 0;
            $totalAccountSlots = $user->total_account_slots ?? 0;
        } elseif ($user->parent_business_id) {
            // User is employee under Business account
            $businessAccount = User::find($user->parent_business_id);

            if (!$businessAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business account not found'
                ], 404);
            }

            $businessAccountId = $businessAccount->id;
            $totalCardQuota = $businessAccount->total_card_quota ?? 0;
            $totalAccountSlots = $businessAccount->total_account_slots ?? 0;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their employees can access this endpoint'
            ], 403);
        }

        // Count actual card orders
        $orderedCardsCount = NfcCard::where('business_account_id', $businessAccountId)
            ->where('subscription_plan', 'business')
            ->count();

        // Count employees
        $employeesCount = User::where('parent_business_id', $businessAccountId)->count();

        // Calculate available quota
        $availableCardQuota = $totalCardQuota - $orderedCardsCount;
        $availableAccountSlots = $totalAccountSlots - $employeesCount;

        return response()->json([
            'success' => true,
            'data' => [
                'total_card_quota' => $totalCardQuota,
                'ordered_cards_count' => $orderedCardsCount,
                'available_card_quota' => $availableCardQuota,
                'business_account_id' => $businessAccountId,
                'account_info' => [
                    'total_account_slots' => $totalAccountSlots,
                    'employees_count' => $employeesCount,
                    'available_account_slots' => $availableAccountSlots
                ]
            ]
        ]);
    }
}
```

---

### 2. GET /api/business/employees

**Purpose**: Get list of employees with quota information

**Authentication**: Required

**Authorization**: Business account only (NOT employees)

**Response**:

```json
{
    "success": true,
    "data": {
        "employees": [
            {
                "id": 456,
                "full_name": "Jane Smith",
                "email": "jane@company.com",
                "job_title": "Sales Manager",
                "created_at": "2025-01-01T10:00:00Z"
            }
        ],
        "quota_info": {
            "total_account_slots": 50,
            "employees_count": 8,
            "available_account_slots": 42,
            "total_card_quota": 50,
            "ordered_cards_count": 12,
            "available_card_quota": 38
        }
    }
}
```

**Backend Implementation**:

```php
public function getEmployees(Request $request)
{
    $user = auth()->user();

    // Only Business account owners can view employees
    if ($user->subscription_plan !== 'business' || $user->parent_business_id) {
        return response()->json([
            'success' => false,
            'message' => 'Only Business account owners can access this endpoint'
        ], 403);
    }

    $businessAccountId = $user->id;

    // Get employees
    $employees = User::where('parent_business_id', $businessAccountId)
        ->select('id', 'full_name', 'email', 'job_title', 'phone', 'created_at', 'subscription_active')
        ->orderBy('created_at', 'desc')
        ->get();

    // Count cards ordered
    $orderedCardsCount = NfcCard::where('business_account_id', $businessAccountId)
        ->where('subscription_plan', 'business')
        ->count();

    // Calculate quota
    $totalCardQuota = $user->total_card_quota ?? 0;
    $totalAccountSlots = $user->total_account_slots ?? 0;
    $employeesCount = $employees->count();

    return response()->json([
        'success' => true,
        'data' => [
            'employees' => $employees,
            'quota_info' => [
                'total_account_slots' => $totalAccountSlots,
                'employees_count' => $employeesCount,
                'available_account_slots' => $totalAccountSlots - $employeesCount,
                'total_card_quota' => $totalCardQuota,
                'ordered_cards_count' => $orderedCardsCount,
                'available_card_quota' => $totalCardQuota - $orderedCardsCount
            ]
        ]
    ]);
}
```

---

### 3. POST /api/business/employees

**Purpose**: Create new employee under Business account

**Authentication**: Required

**Authorization**: Business account only

**Request Body**:

```json
{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@company.com",
    "phone": "+60 12-345 6789",
    "job_title": "Sales Manager",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
}
```

**Response (Success)**:

```json
{
    "success": true,
    "message": "Employee created successfully",
    "data": {
        "employee": {
            "id": 456,
            "full_name": "Jane Smith",
            "email": "jane@company.com"
        },
        "remaining_slots": 42
    }
}
```

**Response (Quota Exceeded)**:

```json
{
    "success": false,
    "message": "Cannot create employee. Account slots quota exceeded.",
    "data": {
        "total_account_slots": 50,
        "employees_count": 50,
        "available_account_slots": 0
    }
}
```

**Backend Implementation**:

```php
public function createEmployee(Request $request)
{
    $user = auth()->user();

    // Validate user is Business account
    if ($user->subscription_plan !== 'business' || $user->parent_business_id) {
        return response()->json([
            'success' => false,
            'message' => 'Only Business account owners can create employees'
        ], 403);
    }

    // Check quota
    $totalAccountSlots = $user->total_account_slots ?? 0;
    $currentEmployeesCount = User::where('parent_business_id', $user->id)->count();

    if ($currentEmployeesCount >= $totalAccountSlots) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot create employee. Account slots quota exceeded.',
            'data' => [
                'total_account_slots' => $totalAccountSlots,
                'employees_count' => $currentEmployeesCount,
                'available_account_slots' => 0
            ]
        ], 403);
    }

    // Validate request
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:50',
        'job_title' => 'nullable|string|max:255',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Create employee
    $employee = new User();
    $employee->first_name = $validated['first_name'];
    $employee->last_name = $validated['last_name'];
    $employee->full_name = $validated['first_name'] . ' ' . $validated['last_name'];
    $employee->email = $validated['email'];
    $employee->phone = $validated['phone'];
    $employee->job_title = $validated['job_title'];
    $employee->password = Hash::make($validated['password']);
    $employee->subscription_plan = 'business';
    $employee->subscription_active = true;
    $employee->parent_business_id = $user->id;
    $employee->company = $user->company; // Inherit company from Business account
    $employee->save();

    return response()->json([
        'success' => true,
        'message' => 'Employee created successfully',
        'data' => [
            'employee' => [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'job_title' => $employee->job_title
            ],
            'remaining_slots' => $totalAccountSlots - ($currentEmployeesCount + 1)
        ]
    ], 201);
}
```

---

### 4. POST /api/nfc-cards

**Purpose**: Order new NFC card (Business Plan)

**Authentication**: Required

**Authorization**: Business account OR employee under Business account

**Request Body**:

```json
{
    "subscription_plan": "business",
    "name": "John Doe",
    "position": "CEO",
    "contact_number": "+60 12-345 6789",
    "email": "john@company.com",
    "website": "https://company.com",
    "business_address": "123 Business Street",
    "delivery_address": "456 Delivery Avenue",
    "company_logo": "https://...",
    "design_method": "template",
    "selected_template": "business"
}
```

**Response (Success)**:

```json
{
    "success": true,
    "message": "NFC Card order placed successfully",
    "data": {
        "card_id": 789,
        "order_number": "ORD-20250112-789",
        "status": "pending"
    }
}
```

**Response (Quota Exceeded)**:

```json
{
    "success": false,
    "message": "Cannot order card. Card quota exceeded.",
    "data": {
        "total_card_quota": 50,
        "ordered_cards_count": 50,
        "available_card_quota": 0
    }
}
```

**Backend Implementation**:

```php
namespace App\Http\Controllers;

use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Http\Request;

class NfcCardController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        // Determine business account
        if ($user->subscription_plan === 'business' && !$user->parent_business_id) {
            $businessAccountId = $user->id;
            $totalCardQuota = $user->total_card_quota ?? 0;
        } elseif ($user->parent_business_id) {
            $businessAccount = User::find($user->parent_business_id);
            $businessAccountId = $businessAccount->id;
            $totalCardQuota = $businessAccount->total_card_quota ?? 0;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and employees can order Business Plan cards'
            ], 403);
        }

        // Check quota
        $orderedCardsCount = NfcCard::where('business_account_id', $businessAccountId)
            ->where('subscription_plan', 'business')
            ->count();

        if ($orderedCardsCount >= $totalCardQuota) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot order card. Card quota exceeded.',
                'data' => [
                    'total_card_quota' => $totalCardQuota,
                    'ordered_cards_count' => $orderedCardsCount,
                    'available_card_quota' => 0
                ]
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'subscription_plan' => 'required|in:business',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'contact_number' => 'required|string|max:50',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'business_address' => 'required|string|max:500',
            'delivery_address' => 'required|string|max:500',
            'company_logo' => 'nullable|string',
            'design_method' => 'required|in:template,custom',
            'selected_template' => 'nullable|string'
        ]);

        // Create card order
        $card = NfcCard::create([
            'user_id' => $user->id,
            'business_account_id' => $businessAccountId,
            'subscription_plan' => 'business',
            'order_number' => 'ORD-' . date('Ymd') . '-' . rand(1000, 9999),
            'status' => 'pending',
            'name' => $validated['name'],
            'position' => $validated['position'],
            'contact_number' => $validated['contact_number'],
            'email' => $validated['email'],
            'website' => $validated['website'],
            'business_address' => $validated['business_address'],
            'delivery_address' => $validated['delivery_address'],
            'company_logo' => $validated['company_logo'],
            'design_method' => $validated['design_method'],
            'selected_template' => $validated['selected_template']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'NFC Card order placed successfully',
            'data' => [
                'card_id' => $card->id,
                'order_number' => $card->order_number,
                'status' => $card->status
            ]
        ], 201);
    }
}
```

---

## Routes Configuration

**Location**: `routes/api.php`

```php
// Business User Routes (authenticated)
Route::middleware(['auth:sanctum'])->prefix('business')->group(function () {
    // Quota Information
    Route::get('/card-quota', [Business\QuotaController::class, 'getCardQuota']);

    // Employee Management
    Route::get('/employees', [Business\EmployeeController::class, 'getEmployees']);
    Route::post('/employees', [Business\EmployeeController::class, 'createEmployee']);
    Route::delete('/employees/{id}', [Business\EmployeeController::class, 'deleteEmployee']);
});

// NFC Card Orders
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/nfc-cards', [NfcCardController::class, 'store']);
});
```

---

## Frontend Integration

### BusinessCardManagement.vue

```javascript
// Load card quota
async function loadCardQuota() {
    try {
        const { $api } = useNuxtApp();
        const response = await $api.get("/business/card-quota");

        if (response.success) {
            cardQuotaInfo.total = response.data.total_card_quota;
            cardQuotaInfo.used = response.data.ordered_cards_count;
            cardQuotaInfo.available = response.data.available_card_quota;
        }
    } catch (error) {
        console.error("Failed to load card quota:", error);
    }
}
```

### BusinessEmployeeManagement.vue

```javascript
// Load employees with quota
async function loadEmployees() {
    try {
        const { $api } = useNuxtApp();
        const response = await $api.get("/business/employees");

        if (response.success) {
            employees.value = response.data.employees;

            // Update quota info
            totalAccountSlots.value =
                response.data.quota_info.total_account_slots;
            employeesCount.value = response.data.quota_info.employees_count;
            orderedCardsCount.value =
                response.data.quota_info.ordered_cards_count;
            totalCardQuota.value = response.data.quota_info.total_card_quota;
        }
    } catch (error) {
        console.error("Failed to load employees:", error);
    }
}
```

---

## Summary

**Business User APIs**:

1. **GET /api/business/card-quota** - View card quota (Business account + employees)
2. **GET /api/business/employees** - List employees with quota (Business account only)
3. **POST /api/business/employees** - Create employee with quota check
4. **POST /api/nfc-cards** - Order card with quota check

**Key Validation**:

-   Employee creation: Check `available_account_slots > 0`
-   Card ordering: Check `available_card_quota > 0`
-   Both Business account and employees can order cards
-   Only Business account can create employees
