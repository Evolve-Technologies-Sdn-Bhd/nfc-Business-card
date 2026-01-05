# Profile Builder Mock Data

This directory contains mock data files representing the three subscription tiers for the NFC Business Card Profile Builder. These files are used for seeding the database and testing UI behavior under different plan restrictions.

## Subscription Tiers & Restrictions

### 1. Basic Plan (`basic_user_mock.json`)

The entry-level plan with significant restrictions.

-   **Quantity Limits:**
    -   Social Links: Max 2
    -   Bio Length: Max 250 chars
    -   Services: 0 (Restricted)
    -   Gallery Images: 0 (Restricted)
-   **Restricted Sections:**
    -   Company (Except basic details)
    -   Services
    -   Portfolio
    -   Blog
-   **Features:** Only essential fields (Name, Job, Contact, Address).

### 2. Premium Plan (`premium_user_mock.json`)

The mid-tier plan for professionals.

-   **Quantity Limits:**
    -   Social Links: Max 5
    -   Services: Max 6
    -   Gallery Images: Max 6
    -   Bio Length: Max 500 chars
-   **Available Sections:**
    -   Profile (Expanded fields like Cover Banner, Pronouns)
    -   Company (Basic info, Logo, Description)
    -   Services (Standard features)
    -   Portfolio (Projects, Gallery)
-   **Restricted:**
    -   Business-only fields (SSM No, Staff ID, Team Members)
    -   Blog Section
    -   Advanced Analytics

### 3. Business Plan (`business_user_mock.json`)

The top-tier plan for companies and teams.

-   **Quantity Limits:** Unlimited
-   **All Features Unlocked:**
    -   Full Company Details (SSM, Video, Awards)
    -   Team Members Management
    -   Advanced Services features (Video, Brochure PDF, Booking)
    -   Full Blog System
    -   Marketing Tools (UTM parameters, Click Analytics)
    -   Advanced Design Options

## Usage

These JSON files can be loaded by the `ProfileBuilderSeeder` or used directly in frontend tests.

### PHP Usage (Seeder)

```php
$json = File::get(database_path('seeders/mock_data/business_user_mock.json'));
$data = json_decode($json, true);
// Use $data to populate LandingPage model
```

### Frontend Usage (Testing)

Import these files in your Vue components or test suites to simulate different user states.
