<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateFieldValidationSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // ============ PROFILE TAB ============
            'name' => [
                'placeholder' => 'e.g., John Smith',
                'validation_rules' => json_encode(['min' => 2, 'max' => 100]),
                'is_required' => true,
            ],
            'position' => [
                'placeholder' => 'e.g., Senior Software Engineer',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'pronouns' => [
                'placeholder' => 'e.g., He/Him, She/Her, They/Them',
                'validation_rules' => json_encode(['max' => 30]),
            ],
            'qualification' => [
                'placeholder' => 'e.g., MBA, PhD, CPA, PMP',
                'validation_rules' => json_encode(['max' => 150]),
            ],
            'bio' => [
                'placeholder' => 'Tell people about yourself, your experience and what you do...',
                'validation_rules' => json_encode(['max' => 1000]),
            ],
            'tagline' => [
                'placeholder' => 'e.g., Building the future, one line of code at a time',
                'validation_rules' => json_encode(['max' => 150]),
            ],
            'contactNumber' => [
                'placeholder' => 'e.g., +60 12-345 6789',
                'validation_rules' => json_encode(['max' => 20]),
            ],
            'emailAddress' => [
                'placeholder' => 'e.g., john@company.com',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'website' => [
                'placeholder' => 'e.g., https://www.yourwebsite.com',
                'validation_rules' => json_encode(['max' => 255]),
            ],
            'address' => [
                'placeholder' => 'e.g., 123 Main Street, City, Country',
                'validation_rules' => json_encode(['max' => 300]),
            ],
            
            // ============ COMPANY TAB ============
            'companyName' => [
                'placeholder' => 'e.g., ABC Technology Sdn Bhd',
                'validation_rules' => json_encode(['max' => 150]),
            ],
            'companyRegistrationNo' => [
                'placeholder' => 'e.g., 123456-A',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'companyDepartment' => [
                'placeholder' => 'e.g., Engineering, Marketing, Sales',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'companyDescription' => [
                'placeholder' => 'Describe your company, services, and what makes you unique...',
                'validation_rules' => json_encode(['max' => 2000]),
            ],
            'companyVideo' => [
                'placeholder' => 'e.g., https://youtube.com/watch?v=xxxxx',
                'validation_rules' => json_encode(['max' => 500]),
            ],
            'industry' => [
                'placeholder' => 'e.g., Technology, Healthcare, Finance',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'establishedYear' => [
                'placeholder' => 'e.g., 2015',
                'validation_rules' => json_encode(['min' => 1900, 'max' => 2030]),
            ],
            'employeeCount' => [
                'placeholder' => 'e.g., 50-100 employees',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'addressName' => [
                'placeholder' => 'e.g., Menara XYZ, Level 10',
                'validation_rules' => json_encode(['max' => 150]),
            ],
            'addressStreet' => [
                'placeholder' => 'e.g., Jalan Ampang 123',
                'validation_rules' => json_encode(['max' => 200]),
            ],
            'addressArea' => [
                'placeholder' => 'e.g., Bangsar, KLCC',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'addressCityState' => [
                'placeholder' => 'e.g., Kuala Lumpur, Selangor',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'addressCountry' => [
                'placeholder' => 'e.g., Malaysia',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'postalCode' => [
                'placeholder' => 'e.g., 50450',
                'validation_rules' => json_encode(['max' => 20]),
            ],
            'mapUrl' => [
                'placeholder' => 'e.g., https://maps.google.com/?q=...',
                'validation_rules' => json_encode(['max' => 500]),
            ],
            'coordinates' => [
                'placeholder' => 'e.g., 3.1390, 101.6869 (latitude, longitude)',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'phoneLabel' => [
                'placeholder' => 'e.g., Office, Mobile, Hotline',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'phoneNumber' => [
                'placeholder' => 'e.g., +60 3-1234 5678',
                'validation_rules' => json_encode(['max' => 20]),
            ],
            'companyWhatsapp' => [
                'placeholder' => 'e.g., +60123456789 (without spaces)',
                'validation_rules' => json_encode(['max' => 20]),
            ],
            
            // ============ SERVICES TAB ============
            'serviceName' => [
                'placeholder' => 'e.g., Web Development',
                'validation_rules' => json_encode(['max' => 150]),
            ],
            'serviceCategory' => [
                'placeholder' => 'e.g., Development, Consulting, Design',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'serviceDescription' => [
                'placeholder' => 'Describe what this service includes...',
                'validation_rules' => json_encode(['max' => 2000]),
            ],
            'servicePrice' => [
                'placeholder' => 'e.g., RM 500 or From RM 299',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'serviceOldPrice' => [
                'placeholder' => 'e.g., RM 799 (original price)',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'serviceDuration' => [
                'placeholder' => 'e.g., 2 hours, 1 day, 2 weeks',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'bookingUrl' => [
                'placeholder' => 'e.g., https://calendly.com/yourname',
                'validation_rules' => json_encode(['max' => 500]),
            ],
            
            // ============ PORTFOLIO TAB ============
            'portfolioTitle' => [
                'placeholder' => 'e.g., E-commerce Website for ABC Corp',
                'validation_rules' => json_encode(['max' => 200]),
            ],
            'portfolioDescription' => [
                'placeholder' => 'Describe the project, challenges, and solutions...',
                'validation_rules' => json_encode(['max' => 2000]),
            ],
            'portfolioCategory' => [
                'placeholder' => 'e.g., Web Design, Mobile App, Branding',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'projectUrl' => [
                'placeholder' => 'e.g., https://www.projectwebsite.com',
                'validation_rules' => json_encode(['max' => 500]),
            ],
            'dateCompleted' => [
                'placeholder' => 'e.g., December 2024',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'clientName' => [
                'placeholder' => 'e.g., ABC Corporation',
                'validation_rules' => json_encode(['max' => 150]),
            ],
            'location' => [
                'placeholder' => 'e.g., Kuala Lumpur, Malaysia',
                'validation_rules' => json_encode(['max' => 150]),
            ],
            
            // ============ BLOG TAB ============
            'blogTitle' => [
                'placeholder' => 'e.g., How to Build a Successful Business',
                'validation_rules' => json_encode(['max' => 255]),
            ],
            'blogSlug' => [
                'placeholder' => 'e.g., how-to-build-successful-business',
                'validation_rules' => json_encode(['max' => 255]),
            ],
            'blogCategory' => [
                'placeholder' => 'e.g., Business, Technology, Lifestyle',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'authorName' => [
                'placeholder' => 'e.g., John Smith',
                'validation_rules' => json_encode(['max' => 100]),
            ],
            'publishedDate' => [
                'placeholder' => 'e.g., 2024-12-01',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'readingTime' => [
                'placeholder' => 'e.g., 5 min read',
                'validation_rules' => json_encode(['max' => 30]),
            ],
            'blogContent' => [
                'placeholder' => 'Write your blog post content here...',
                'validation_rules' => json_encode(['max' => 50000]),
            ],
            'externalLink' => [
                'placeholder' => 'e.g., https://medium.com/@yourname/article',
                'validation_rules' => json_encode(['max' => 500]),
            ],
            
            // ============ LINKS TAB ============
            'appointmentLink' => [
                'placeholder' => 'e.g., https://calendly.com/yourname',
                'validation_rules' => json_encode(['max' => 500]),
            ],
            'paymentButtonText' => [
                'placeholder' => 'e.g., Pay Now, Make Payment',
                'validation_rules' => json_encode(['max' => 50]),
            ],
            'paymentButtonUrl' => [
                'placeholder' => 'e.g., https://pay.stripe.com/...',
                'validation_rules' => json_encode(['max' => 500]),
            ],
        ];
        
        foreach ($fields as $fieldKey => $data) {
            $updateData = [];
            
            if (isset($data['placeholder'])) {
                $updateData['placeholder'] = $data['placeholder'];
            }
            if (isset($data['validation_rules'])) {
                $updateData['validation_rules'] = $data['validation_rules'];
            }
            if (isset($data['is_required'])) {
                $updateData['is_required'] = $data['is_required'];
            }
            
            if (!empty($updateData)) {
                DB::table('profile_builder_fields')
                    ->where('field_key', $fieldKey)
                    ->update($updateData);
            }
        }
        
        $this->command->info('Field placeholders and validation rules updated successfully!');
    }
}
