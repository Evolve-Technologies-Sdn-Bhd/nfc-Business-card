# Profile Builder API Testing Guide

## 🔐 Authentication Required

All endpoints require authentication via Sanctum token.

### Get Auth Token
```bash
# Login first
POST http://localhost:8000/api/login
{
  "email": "your@email.com",
  "password": "password"
}

# Response will include token:
{
  "success": true,
  "token": "YOUR_TOKEN_HERE",
  "user": {...}
}
```

---

## 📋 User Endpoints (Authenticated)

### 1. Get Sections with Fields (Filtered by Plan)

**Request:**
```http
GET /api/profile-builder-sections?plan=business
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "section_key": "profile",
      "section_name": "Profile",
      "icon": "heroicons:user",
      "category": "general",
      "description": "Personal information and bio",
      "display_order": 1,
      "available_plans": ["basic", "premium", "business"],
      "fields": [...]
    },
    {
      "section_key": "portfolio",
      "section_name": "Portfolio",
      "icon": "heroicons:briefcase",
      "category": "general",
      "description": "Showcase your projects and work samples",
      "display_order": 5,
      "available_plans": ["premium", "business"],
      "fields": [
        {
          "id": 1,
          "tab": "portfolio",
          "field_key": "portfolio_title",
          "field_type": "text",
          "label": "Portfolio Title",
          "placeholder": "My Work",
          "help_text": "Main title for your portfolio section",
          "is_required": false,
          "is_visible": true,
          "validation_rules": {"max": 100},
          "available_plans": ["premium", "business"],
          "display_order": 1,
          "config": {}
        }
      ]
    }
  ]
}
```

### 2. Get All Fields (Grouped by Tab)

**Request:**
```http
GET /api/profile-builder-fields?plan=business
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "data": {
    "profile": [...],
    "company": [...],
    "services": [...],
    "links": [...],
    "portfolio": [...],
    "blog": [...]
  }
}
```

### 3. Get Fields for Specific Tab

**Request:**
```http
GET /api/profile-builder-fields?tab=portfolio
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "tab": "portfolio",
      "field_key": "portfolio_title",
      "field_type": "text",
      ...
    }
  ]
}
```

---

## 🔧 Admin Endpoints (Admin Only)

### 1. Get Available Sections

**Request:**
```http
GET /api/admin/profile-builder/sections
Authorization: Bearer ADMIN_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "data": {
    "sections": [
      {
        "key": "profile",
        "name": "Profile",
        "icon": "heroicons:user",
        "category": "general",
        "description": "Personal information and bio",
        "display_order": 1,
        "available_plans": ["basic", "premium", "business"],
        "has_fields": true
      },
      {
        "key": "portfolio",
        "name": "Portfolio",
        "icon": "heroicons:briefcase",
        "category": "general",
        "description": "Showcase your projects and work samples",
        "display_order": 5,
        "available_plans": ["premium", "business"],
        "has_fields": true
      }
    ],
    "field_types": {
      "text": "Text Input",
      "email": "Email",
      "richtext": "Rich Text Editor",
      "repeater": "Repeatable Fields",
      ...
    }
  }
}
```

### 2. Get Sections with Fields

**Request:**
```http
GET /api/admin/profile-builder/sections-with-fields
Authorization: Bearer ADMIN_TOKEN_HERE
```

**Response:** Same as user's `/api/profile-builder-sections` but without plan filtering.

### 3. Get All Fields

**Request:**
```http
GET /api/admin/profile-builder/fields
Authorization: Bearer ADMIN_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "data": {
    "profile": [...],
    "company": [...],
    "portfolio": [...]
  }
}
```

### 4. Create New Field

**Request:**
```http
POST /api/admin/profile-builder/fields
Authorization: Bearer ADMIN_TOKEN_HERE
Content-Type: application/json

{
  "tab": "portfolio",
  "field_key": "awards",
  "field_type": "repeater",
  "label": "Awards & Recognition",
  "placeholder": null,
  "help_text": "Add your awards and achievements",
  "is_required": false,
  "is_visible": true,
  "validation_rules": {},
  "available_plans": ["premium", "business"],
  "display_order": 4,
  "config": {
    "max_items": 10,
    "sub_fields": [
      {
        "key": "title",
        "label": "Award Title",
        "type": "text",
        "required": true
      },
      {
        "key": "year",
        "label": "Year",
        "type": "number"
      },
      {
        "key": "description",
        "label": "Description",
        "type": "textarea"
      }
    ]
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Field created successfully",
  "data": {
    "id": 10,
    "tab": "portfolio",
    "field_key": "awards",
    ...
  }
}
```

### 5. Update Field

**Request:**
```http
PUT /api/admin/profile-builder/fields/10
Authorization: Bearer ADMIN_TOKEN_HERE
Content-Type: application/json

{
  "label": "Awards & Achievements",
  "display_order": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Field updated successfully",
  "data": {...}
}
```

### 6. Delete Field

**Request:**
```http
DELETE /api/admin/profile-builder/fields/10
Authorization: Bearer ADMIN_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "message": "Field deleted successfully"
}
```

---

## 🧪 Testing with PowerShell

### Setup
```powershell
# Get token
$loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method POST -Body (@{email="admin@example.com"; password="password"} | ConvertTo-Json) -ContentType "application/json"
$token = $loginResponse.token

# Create headers
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}
```

### Test Endpoints
```powershell
# Get sections (user)
$sections = Invoke-RestMethod -Uri "http://localhost:8000/api/profile-builder-sections?plan=business" -Headers $headers
$sections | ConvertTo-Json -Depth 10

# Get available sections (admin)
$adminSections = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/profile-builder/sections" -Headers $headers
$adminSections | ConvertTo-Json -Depth 10

# Get fields for portfolio tab
$portfolioFields = Invoke-RestMethod -Uri "http://localhost:8000/api/profile-builder-fields?tab=portfolio" -Headers $headers
$portfolioFields | ConvertTo-Json -Depth 10

# Create new field (admin)
$newField = @{
    tab = "portfolio"
    field_key = "test_field"
    field_type = "text"
    label = "Test Field"
    is_visible = $true
    display_order = 99
} | ConvertTo-Json

$createResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/profile-builder/fields" -Method POST -Body $newField -Headers (@{Authorization="Bearer $token"; "Content-Type"="application/json"})
$createResponse | ConvertTo-Json
```

---

## ✅ Expected Results

### For Business Plan User
Should see sections:
- ✅ Profile
- ✅ Company & Team
- ✅ Services
- ✅ Social Media & Links
- ✅ Portfolio (NEW)
- ✅ Blog (NEW)
- ✅ Design

### For Premium Plan User
Should see sections:
- ✅ Profile
- ✅ Company & Team
- ✅ Services
- ✅ Social Media & Links
- ✅ Portfolio (NEW)
- ✅ Design

### For Basic Plan User
Should see sections:
- ✅ Profile
- ✅ Social Media & Links
- ✅ Design

---

## 📝 Field Types Available

All 18 field types are now supported:
1. text
2. email
3. tel
4. url
5. textarea
6. richtext
7. number
8. date
9. select
10. image
11. video
12. file
13. gallery
14. repeater
15. toggle
16. checkbox
17. icon
18. color

---

## 🚨 Common Errors

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
**Solution**: Add valid Bearer token to Authorization header

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```
**Solution**: Ensure user has admin role for admin endpoints

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_type": ["The field type field must be one of: text, email, ..."]
  }
}
```
**Solution**: Check request body matches validation rules

---

Last Updated: November 24, 2025
