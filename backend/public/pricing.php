<?php
/**
 * Pricing Page
 * 
 * Display pricing plans and initiate payment
 */

define('FIUU_INIT', true);
require_once __DIR__ . '/config/fiuu-config.php';

// Define pricing plans
$plans = [
    [
        'id' => 'basic',
        'name' => 'Basic Plan',
        'price' => 29.00,
        'features' => [
            'Single NFC Business Card',
            'Basic Profile Information',
            'Contact Sharing',
            'Analytics Dashboard',
            '24/7 Support',
        ],
    ],
    [
        'id' => 'professional',
        'name' => 'Professional Plan',
        'price' => 59.00,
        'popular' => true,
        'features' => [
            'Up to 3 NFC Business Cards',
            'Advanced Profile Customization',
            'Social Media Integration',
            'Detailed Analytics',
            'Custom Branding',
            'Priority Support',
        ],
    ],
    [
        'id' => 'enterprise',
        'name' => 'Enterprise Plan',
        'price' => 149.00,
        'features' => [
            'Unlimited NFC Business Cards',
            'Team Management',
            'API Access',
            'Advanced Analytics & Reporting',
            'Custom Domain',
            'Dedicated Account Manager',
            'White Label Options',
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - NFC Business Card</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: white;
            font-size: 48px;
            margin-bottom: 16px;
        }
        .subtitle {
            text-align: center;
            color: rgba(255,255,255,0.9);
            font-size: 20px;
            margin-bottom: 48px;
        }
        .plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            margin-bottom: 48px;
        }
        .plan {
            background: white;
            border-radius: 16px;
            padding: 32px;
            position: relative;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .plan:hover {
            transform: translateY(-8px);
        }
        .plan.popular {
            border: 3px solid #ffc107;
        }
        .popular-badge {
            position: absolute;
            top: -12px;
            right: 24px;
            background: #ffc107;
            color: #333;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .plan-name {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
        }
        .price {
            font-size: 48px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        .price-currency {
            font-size: 24px;
            color: #6c757d;
        }
        .features {
            list-style: none;
            margin: 24px 0;
        }
        .features li {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
            color: #333;
        }
        .features li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 12px;
        }
        .features li:last-child {
            border-bottom: none;
        }
        .button {
            display: block;
            width: 100%;
            padding: 16px;
            background: #667eea;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #5568d3;
        }
        .back-link {
            text-align: center;
            margin-top: 32px;
        }
        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Choose Your Plan</h1>
        <p class="subtitle">Select the perfect plan for your business needs</p>
        
        <div class="plans">
            <?php foreach ($plans as $plan): ?>
                <div class="plan <?php echo isset($plan['popular']) ? 'popular' : ''; ?>">
                    <?php if (isset($plan['popular'])): ?>
                        <div class="popular-badge">Most Popular</div>
                    <?php endif; ?>
                    
                    <div class="plan-name"><?php echo $plan['name']; ?></div>
                    <div class="price">
                        <span class="price-currency">MYR</span>
                        <?php echo number_format($plan['price'], 2); ?>
                    </div>
                    <p style="color: #6c757d; margin-bottom: 16px;">per month</p>
                    
                    <ul class="features">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li><?php echo $feature; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <form action="payment/payment-form.php" method="POST">
                        <input type="hidden" name="user_id" value="1">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <input type="hidden" name="plan_name" value="<?php echo $plan['name']; ?>">
                        <input type="hidden" name="amount" value="<?php echo $plan['price']; ?>">
                        <input type="hidden" name="bill_name" value="Test Customer">
                        <input type="hidden" name="bill_email" value="customer@example.com">
                        <input type="hidden" name="bill_mobile" value="+60123456789">
                        <button type="submit" class="button">Get Started</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="back-link">
            <a href="<?php echo DASHBOARD_URL; ?>">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
