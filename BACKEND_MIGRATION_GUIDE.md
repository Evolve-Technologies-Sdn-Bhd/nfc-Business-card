# Backend Migration Guide

## Required Changes for Profile Builder Integration

### 1. Database Migration

Create a migration to add new columns to the users/profiles table:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Basic Info (some may already exist)
            $table->string('qualification')->nullable()->after('title');
            $table->text('bio')->nullable()->after('qualification');
            
            // Company Info
            $table->string('company_logo', 500)->nullable();
            $table->string('company_logo_text', 10)->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_registration_no', 100)->nullable();
            $table->string('company_department')->nullable();
            
            // Address Info
            $table->string('address_name')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_area')->nullable();
            $table->string('address_city_state')->nullable();
            $table->string('address_country', 100)->nullable();
            $table->string('address_map_url', 500)->nullable();
            
            // Contact Methods
            $table->string('phone_number', 50)->nullable();
            $table->string('phone_label', 50)->default('Phone');
            $table->string('email_address')->nullable();
            $table->string('email_label', 50)->default('Email');
            $table->string('whatsapp_number', 50)->nullable();
            $table->string('whatsapp_label', 50)->default('WhatsApp');
            $table->string('website_url')->nullable();
            $table->string('website_label', 50)->default('Website');
            
            // JSON Arrays
            $table->json('stats')->nullable();
            $table->json('services')->nullable();
            $table->json('social_links')->nullable();
            $table->json('team_members')->nullable();
            
            // Design Settings
            $table->string('profile_style', 50)->default('classic');
            $table->string('theme', 50)->default('minimal');
            $table->string('background_color', 7)->default('#FFFFFF');
            $table->string('text_color', 7)->default('#000000');
            $table->string('font', 50)->default('inter');
            $table->string('button_style', 50)->default('solid');
            $table->boolean('show_watermark')->default(true);
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'qualification', 'bio',
                'company_logo', 'company_logo_text', 'company_name', 
                'company_registration_no', 'company_department',
                'address_name', 'address_street', 'address_area', 
                'address_city_state', 'address_country', 'address_map_url',
                'phone_number', 'phone_label', 'email_address', 'email_label',
                'whatsapp_number', 'whatsapp_label', 'website_url', 'website_label',
                'stats', 'services', 'social_links', 'team_members',
                'profile_style', 'theme', 'background_color', 'text_color',
                'font', 'button_style', 'show_watermark'
            ]);
        });
    }
};
```

### 2. Update Profile Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        // Basic Info
        'name', 'title', 'qualification', 'bio', 
        'phone', 'email', 'website', 'profile_image',
        
        // Company Info
        'company_logo', 'company_logo_text', 'company_name',
        'company_registration_no', 'company_department',
        
        // Address Info
        'address_name', 'address_street', 'address_area',
        'address_city_state', 'address_country', 'address_map_url',
        
        // Contact Methods
        'phone_number', 'phone_label', 'email_address', 'email_label',
        'whatsapp_number', 'whatsapp_label', 'website_url', 'website_label',
        
        // JSON Arrays
        'stats', 'services', 'social_links', 'team_members',
        
        // Design Settings
        'profile_style', 'theme', 'background_color', 'text_color',
        'font', 'button_style', 'show_watermark',
    ];

    protected $casts = [
        'stats' => 'array',
        'services' => 'array',
        'social_links' => 'array',
        'team_members' => 'array',
        'show_watermark' => 'boolean',
    ];

    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 3. Update Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get current user's profile
     */
    public function show()
    {
        $profile = Auth::user()->profile;
        
        return response()->json([
            'success' => true,
            'profile' => $profile
        ]);
    }

    /**
     * Get a user's public profile by username
     */
    public function showPublic($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $profile = $user->profile;
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'profile' => $profile
        ]);
    }

    /**
     * Update user's profile
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Basic Info
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            
            // Company Info
            'company_logo_text' => 'nullable|string|max:10',
            'company_name' => 'nullable|string|max:255',
            'company_registration_no' => 'nullable|string|max:100',
            'company_department' => 'nullable|string|max:255',
            
            // Address Info
            'address_name' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_area' => 'nullable|string|max:255',
            'address_city_state' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|max:100',
            'address_map_url' => 'nullable|url|max:500',
            
            // Contact Methods
            'phone_number' => 'nullable|string|max:50',
            'phone_label' => 'nullable|string|max:50',
            'email_address' => 'nullable|email|max:255',
            'email_label' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_label' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:255',
            'website_label' => 'nullable|string|max:50',
            
            // JSON Arrays
            'stats' => 'nullable|array|max:3',
            'stats.*.num' => 'required|string|max:20',
            'stats.*.label' => 'required|string|max:50',
            
            'services' => 'nullable|array|max:6',
            'services.*.icon' => 'required|string|max:10',
            'services.*.name' => 'required|string|max:100',
            
            'social_links' => 'nullable|array|max:4',
            'social_links.*.emoji' => 'required|string|max:10',
            'social_links.*.name' => 'required|string|max:50',
            'social_links.*.url' => 'required|url|max:255',
            
            'team_members' => 'nullable|array|max:3',
            'team_members.*.initials' => 'nullable|string|max:3',
            'team_members.*.name' => 'nullable|string|max:255',
            'team_members.*.role' => 'nullable|string|max:100',
            
            // Design Settings
            'profile_style' => 'nullable|in:classic,hero',
            'theme' => 'nullable|string|max:50',
            'background_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'font' => 'nullable|string|max:50',
            'button_style' => 'nullable|string|max:50',
            'show_watermark' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $profile = Auth::user()->profile;
        
        if (!$profile) {
            $profile = new Profile(['user_id' => Auth::id()]);
        }

        $profile->fill($request->all());
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }
}
```

### 4. Update Routes

```php
// routes/api.php

use App\Http\Controllers\ProfileController;

Route::middleware('auth:sanctum')->group(function () {
    // Authenticated user's profile
    Route::get('/user/profile', [ProfileController::class, 'show']);
    Route::put('/user/profile', [ProfileController::class, 'update']);
});

// Public profile by username
Route::get('/user/profile/{username}', [ProfileController::class, 'showPublic']);
```

### 5. File Upload Endpoints

Ensure you have these endpoints for image uploads:

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload/profile-image', [UploadController::class, 'uploadProfileImage']);
    Route::delete('/upload/profile-image', [UploadController::class, 'deleteProfileImage']);
    
    Route::post('/upload/company-logo', [UploadController::class, 'uploadCompanyLogo']);
    Route::delete('/upload/company-logo', [UploadController::class, 'deleteCompanyLogo']);
});
```

### 6. Testing

Test the endpoints:

```bash
# Get current user's profile
GET /api/user/profile
Authorization: Bearer {token}

# Update profile
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe",
  "title": "Software Engineer",
  "email": "john@example.com",
  "stats": [
    { "num": "5+", "label": "Years Experience" },
    { "num": "100+", "label": "Projects" },
    { "num": "99%", "label": "Satisfaction" }
  ],
  "services": [
    { "icon": "💻", "name": "Web Development" }
  ]
}

# Get public profile
GET /api/user/profile/johndoe
```

### 7. Data Migration (Optional)

If you have existing users with old profile data, create a migration script:

```php
<?php

use App\Models\Profile;

// Migrate old 'location' field to new address fields
Profile::whereNotNull('location')->chunk(100, function ($profiles) {
    foreach ($profiles as $profile) {
        $profile->address_street = $profile->location;
        $profile->save();
    }
});

// Set default values for existing profiles
Profile::whereNull('profile_style')->update(['profile_style' => 'classic']);
Profile::whereNull('theme')->update(['theme' => 'minimal']);
Profile::whereNull('show_watermark')->update(['show_watermark' => true]);
```

## Summary

1. ✅ Run database migration
2. ✅ Update Profile model with new fields and casts
3. ✅ Update ProfileController with validation rules
4. ✅ Add API routes
5. ✅ Ensure file upload endpoints exist
6. ✅ Test all endpoints
7. ✅ (Optional) Migrate existing data

After completing these steps, the Profile Builder will be fully integrated with the backend!
