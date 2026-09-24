<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalDocument;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'type' => 'terms_of_service',
                'version' => '1.0.0',
                'effective_date' => now(),
                'content' => $this->getTermsOfServiceContent(),
            ],
            [
                'type' => 'privacy_policy',
                'version' => '1.0.0',
                'effective_date' => now(),
                'content' => $this->getPrivacyPolicyContent(),
            ],
            [
                'type' => 'refund_policy',
                'version' => '1.0.0',
                'effective_date' => now(),
                'content' => $this->getRefundPolicyContent(),
            ],
            [
                'type' => 'acceptable_use',
                'version' => '1.0.0',
                'effective_date' => now(),
                'content' => $this->getAcceptableUseContent(),
            ],
        ];

        foreach ($documents as $doc) {
            $existing = LegalDocument::where('type', $doc['type'])->first();

            if ($existing) {
                $existing->update([
                    'content' => $doc['content'],
                    'version' => $doc['version'],
                    'effective_date' => $doc['effective_date'],
                ]);
                $this->command->info('🔄 Updated: ' . ucwords(str_replace('_', ' ', $doc['type'])) . ' (v' . $doc['version'] . ')');
            } else {
                LegalDocument::create($doc);
                $this->command->info('➕ Created: ' . ucwords(str_replace('_', ' ', $doc['type'])) . ' (v' . $doc['version'] . ')');
            }
        }

        $this->command->info('✅ Legal documents seeded: ' . count($documents) . ' documents.');
    }

    private function getTermsOfServiceContent(): string
    {
        return <<<HTML
<h1>Terms of Service</h1>
<p><strong>Effective Date:</strong> September 2026</p>
<p><strong>Version:</strong> 1.0.0</p>

<h2>1. Agreement to Terms</h2>
<p>By accessing or using the NFC Business Card platform ("Service"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Service.</p>

<h2>2. Description of Service</h2>
<p>NFC Business Card provides digital profile hosting, NFC card management, analytics, and related services for individuals and businesses. Physical NFC cards are optional add-ons subject to separate shipping and fulfillment terms.</p>

<h2>3. User Accounts</h2>
<ul>
    <li>You must be at least 18 years old to create an account.</li>
    <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
    <li>You agree to provide accurate, current, and complete registration information.</li>
    <li>Sharing of account credentials is prohibited unless explicitly permitted under a Business plan.</li>
</ul>

<h2>4. Subscription & Payment</h2>
<ul>
    <li>All fees are in Malaysian Ringgit (MYR) unless otherwise stated.</li>
    <li>Subscriptions renew automatically unless canceled before the renewal date.</li>
    <li>Late or failed payments may result in service suspension.</li>
    <li>Physical NFC card purchases are non-refundable once shipped.</li>
</ul>

<h2>5. Acceptable Use</h2>
<ul>
    <li>You may not use the Service for any illegal, harmful, or offensive purpose.</li>
    <li>You may not infringe on the intellectual property rights of others.</li>
    <li>You may not attempt to reverse-engineer or compromise platform security.</li>
    <li>Profiles promoting violence, discrimination, or fraud will be terminated without refund.</li>
</ul>

<h2>6. Intellectual Property</h2>
<p>All platform trademarks, logos, and design elements are the property of NFC Business Card. You retain ownership of the personal and business content you upload to your profile.</p>

<h2>7. Limitation of Liability</h2>
<p>The Service is provided "as is" without warranties of any kind. We are not liable for any indirect, incidental, special, or consequential damages arising from your use of the Service.</p>

<h2>8. Modifications</h2>
<p>We reserve the right to modify these terms at any time. Material changes will be notified via email or in-app notification at least 14 days before taking effect.</p>

<h2>9. Contact</h2>
<p>For questions regarding these Terms, email legal@nfcbusinesscard.com or call +60 3-0000-0000.</p>
HTML;
    }

    private function getPrivacyPolicyContent(): string
    {
        return <<<HTML
<h1>Privacy Policy</h1>
<p><strong>Effective Date:</strong> September 2026</p>
<p><strong>Version:</strong> 1.0.0</p>

<h2>1. Introduction</h2>
<p>NFC Business Card ("we", "us", or "our") is committed to protecting the privacy of our users ("you" or "your"). This policy explains what personal data we collect, how we use it, and your rights under applicable law.</p>

<h2>2. Data We Collect</h2>
<ul>
    <li><strong>Account Information:</strong> Full name, email address, phone number, billing details, and company information (if applicable).</li>
    <li><strong>Profile Content:</strong> Any information you choose to publish on your digital business card (photo, bio, social links, etc.).</li>
    <li><strong>Analytics Data:</strong> Aggregated and anonymized tap counts, link clicks, visitor locations, and device types.</li>
    <li><strong>Technical Data:</strong> IP address, browser type, device information, and access timestamps for security purposes.</li>
    <li><strong>Payment Data:</strong> Processed securely through our payment gateway (FIUU/Razer). We do not store full credit card numbers on our servers.</li>
</ul>

<h2>3. How We Use Your Data</h2>
<ul>
    <li>To provide, operate, and maintain the Service</li>
    <li>To process transactions and subscriptions</li>
    <li>To send service notifications, invoices, and security alerts</li>
    <li>To improve product features and user experience based on analytics</li>
    <li>To comply with legal obligations and fraud prevention</li>
</ul>
<p>We will never sell your personal data to third parties for marketing purposes without your explicit consent.</p>

<h2>4. Data Sharing</h2>
<p>We share data only with:</p>
<ul>
    <li>Payment processors (transaction processing only)</li>
    <li>Cloud infrastructure providers (secure hosting)</li>
    <li>Law enforcement authorities when required by law</li>
    <li>Analytics tools (anonymized and aggregated)</li>
</ul>

<h2>5. Data Retention</h2>
<ul>
    <li>Account data is retained as long as your account is active plus 90 days.</li>
    <li>Deleted accounts and associated content are permanently removed within 30 days.</li>
    <li>Financial records are retained for 7 years as required by Malaysian tax law.</li>
    <li>Analytics data is retained for 24 months and then aggregated or deleted.</li>
</ul>

<h2>6. Your Rights</h2>
<ul>
    <li>Right of access — request a copy of your personal data</li>
    <li>Right of rectification — correct inaccurate data</li>
    <li>Right to erasure — request deletion of your data</li>
    <li>Right to data portability — export your data</li>
    <li>Right to object — opt out of non-essential processing</li>
</ul>
<p>To exercise these rights, email privacy@nfcbusinesscard.com.</p>

<h2>7. Security</h2>
<p>We implement industry-standard security measures including HTTPS encryption, secure password hashing, regular security audits, and access controls. However, no method of transmission over the Internet is 100% secure.</p>

<h2>8. Cookies & Tracking</h2>
<p>We use essential cookies for authentication and session management. Optional analytics cookies may be used to improve the Service; these may be disabled via your browser settings.</p>

<h2>9. Changes to this Policy</h2>
<p>We may update this Privacy Policy from time to time. Material changes will be notified via email or in-app notice at least 14 days before taking effect.</p>

<h2>10. Contact</h2>
<p>For privacy-related inquiries, contact our Data Protection Officer at privacy@nfcbusinesscard.com or call +60 3-0000-0000.</p>
HTML;
    }

    private function getRefundPolicyContent(): string
    {
        return <<<HTML
<h1>Refund & Cancellation Policy</h1>
<p><strong>Effective Date:</strong> September 2026</p>
<p><strong>Version:</strong> 1.0.0</p>

<h2>1. Subscription Refunds</h2>
<ul>
    <li><strong>Monthly Plans:</strong> No partial-month refunds. Cancellation is effective at the end of the current billing cycle.</li>
    <li><strong>Annual / Prepaid Plans:</strong> Unused full months may be refunded pro-rata minus a 10% administrative fee, provided no more than 50% of the term has elapsed.</li>
    <li><strong>Free Trial:</strong> If you cancel during a free trial period, you will not be charged.</li>
</ul>

<h2>2. Physical NFC Card Refunds</h2>
<ul>
    <li>Custom-printed physical NFC cards are non-refundable once production has started.</li>
    <li>Defective or misprinted cards will be replaced free of charge upon photo evidence submitted within 7 days of delivery.</li>
    <li>Undelivered items confirmed lost by courier will be re-shipped or fully refunded.</li>
</ul>

<h2>3. How to Request a Refund</h2>
<ol>
    <li>Log in to your dashboard and go to <em>Settings → Billing → Request Refund</em>, or email billing@nfcbusinesscard.com with your invoice number.</li>
    <li>Provide a brief reason for the request.</li>
    <li>Approved refunds are processed back to the original payment method within 5–10 business days.</li>
</ol>

<h2>4. Exceptions</h2>
<p>Refunds will not be issued for:</p>
<ul>
    <li>Accounts terminated for violation of Terms of Service</li>
    <li>Digital-only subscription where the Service was accessible and functional</li>
    <li>Dissatisfaction with third-party integrations outside our control</li>
</ul>

<h2>5. Disputes</h2>
<p>If a refund request is denied, you may escalate the case within 14 days for a secondary review by the support manager.</p>
HTML;
    }

    private function getAcceptableUseContent(): string
    {
        return <<<HTML
<h1>Acceptable Use Policy</h1>
<p><strong>Effective Date:</strong> September 2026</p>
<p><strong>Version:</strong> 1.0.0</p>

<h2>1. Purpose</h2>
<p>This policy defines prohibited content and activities on the NFC Business Card platform to ensure a safe, legal, and respectful environment for all users.</p>

<h2>2. Prohibited Content</h2>
<ul>
    <li>Content that is unlawful, threatening, abusive, discriminatory, or promotes violence or harm</li>
    <li>Content infringing on copyrights, trademarks, or other intellectual property rights</li>
    <li>False, misleading, or fraudulent representations of identity or credentials</li>
    <li>Adult, explicit, or NSFW content</li>
    <li>Malicious links, phishing, malware, or malware distribution</li>
    <li>Content that promotes illegal substances, weapons, or dangerous activities</li>
    <li>Spam, repetitive promotional content, or multi-level marketing schemes</li>
</ul>

<h2>3. Prohibited Activities</h2>
<ul>
    <li>Scraping, harvesting, or bulk-copying user profile data without written consent</li>
    <li>Attempting to gain unauthorized access to other users' accounts or data</li>
    <li>Distributing or sharing counterfeit or modified NFC cards for fraudulent use</li>
    <li>Using the platform to impersonate any person, company, or official body</li>
    <li>Excessive load testing or denial-of-service-style traffic against the infrastructure</li>
</ul>

<h2>4. Enforcement</h2>
<ul>
    <li>Reported violations are reviewed within 24–48 hours.</li>
    <li>Minor violations may result in a content-removal warning.</li>
    <li>Serious or repeated violations will result in immediate account termination without refund.</li>
    <li>We may cooperate with law enforcement for illegal activity investigations.</li>
</ul>

<h2>5. Reporting</h2>
<p>To report a profile or user in violation of this policy, email reports@nfcbusinesscard.com with the profile link and a brief description of the issue.</p>
HTML;
    }
}
