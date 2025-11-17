<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Plan Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
            min-width: 160px;
        }
        .info-value {
            color: #1f2937;
            flex: 1;
        }
        .highlight {
            background-color: #fef3c7;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
            border-radius: 4px;
        }
        .notes-section {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .notes-section h3 {
            margin-top: 0;
            color: #4b5563;
            font-size: 16px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
        .badge {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🏢 New Business Plan Request</h1>
            <p>NFCGo Business Account Application</p>
        </div>

        <div class="highlight">
            <strong>Action Required:</strong> A new customer has requested the Business Plan. Please review and contact them within 24 hours.
        </div>

        <div class="info-section">
            <h2 style="color: #1f2937; margin-bottom: 15px;">Company Information</h2>
            
            <div class="info-row">
                <div class="info-label">Company Name:</div>
                <div class="info-value"><strong><?php echo e($data['company_name']); ?></strong></div>
            </div>

            <div class="info-row">
                <div class="info-label">Company Address:</div>
                <div class="info-value"><?php echo e($data['company_address']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">NFC Cards Needed:</div>
                <div class="info-value">
                    <span class="badge"><?php echo e($data['quota']); ?> cards</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h2 style="color: #1f2937; margin-bottom: 15px;">Contact Information</h2>
            
            <div class="info-row">
                <div class="info-label">Contact Person:</div>
                <div class="info-value"><?php echo e($data['contact_person']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><a href="mailto:<?php echo e($data['email']); ?>"><?php echo e($data['email']); ?></a></div>
            </div>

            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value"><a href="tel:<?php echo e($data['phone']); ?>"><?php echo e($data['phone']); ?></a></div>
            </div>
        </div>

        <div class="info-section">
            <h2 style="color: #1f2937; margin-bottom: 15px;">User Account Details</h2>
            
            <div class="info-row">
                <div class="info-label">User ID:</div>
                <div class="info-value">#<?php echo e($data['user_id']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">User Name:</div>
                <div class="info-value"><?php echo e($data['user_name']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">User Email:</div>
                <div class="info-value"><a href="mailto:<?php echo e($data['user_email']); ?>"><?php echo e($data['user_email']); ?></a></div>
            </div>

            <div class="info-row">
                <div class="info-label">Submitted At:</div>
                <div class="info-value"><?php echo e($data['submitted_at']); ?></div>
            </div>
        </div>

        <?php if(isset($data['notes']) && !empty($data['notes'])): ?>
        <div class="notes-section">
            <h3>📝 Additional Notes:</h3>
            <p style="margin: 10px 0 0 0; color: #374151;"><?php echo e($data['notes']); ?></p>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p><strong>NFCGo</strong> - Digital Business Card Platform</p>
            <p>This is an automated notification. Please contact the customer within 24 hours.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\User\Intern\NFCs\nfc-Business-card\backend\resources\views/emails/business-plan-request.blade.php ENDPATH**/ ?>