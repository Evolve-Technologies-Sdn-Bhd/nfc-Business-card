<?php $__env->startComponent('mail::message'); ?>
# Reset Your Password

Hello <?php echo new \Illuminate\Support\EncodedHtmlString($user->name); ?>,

We received a request to reset your password for your account. Click the button below to create a new password:

<?php $__env->startComponent('mail::button', ['url' => $resetUrl]); ?>
Reset Password
<?php echo $__env->renderComponent(); ?>

This link will expire in <?php echo new \Illuminate\Support\EncodedHtmlString($expiryMinutes); ?> minutes.

**Request Details:**
- IP Address: <?php echo new \Illuminate\Support\EncodedHtmlString($requestIp); ?>

- Time: <?php echo new \Illuminate\Support\EncodedHtmlString(now()->format('F j, Y g:i A')); ?>


If you didn't request this password reset, please ignore this email. Your password will remain unchanged.

For security reasons, we recommend:
- Using a strong, unique password
- Enabling two-factor authentication if available
- Not sharing your password with anyone

Thanks,<br>
<?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name')); ?>


---

If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:

<?php echo new \Illuminate\Support\EncodedHtmlString($resetUrl); ?>

<?php echo $__env->renderComponent(); ?><?php /**PATH C:\Users\tanye\nfc-business-card\backend\resources\views/emails/password-reset.blade.php ENDPATH**/ ?>