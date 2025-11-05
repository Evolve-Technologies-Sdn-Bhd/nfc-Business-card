@component('mail::message')
# Reset Your Password

Hello {{ $user->name }},

We received a request to reset your password for your account. Click the button below to create a new password:

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

This link will expire in {{ $expiryMinutes }} minutes.

**Request Details:**
- IP Address: {{ $requestIp }}
- Time: {{ now()->format('F j, Y g:i A') }}

If you didn't request this password reset, please ignore this email. Your password will remain unchanged.

For security reasons, we recommend:
- Using a strong, unique password
- Enabling two-factor authentication if available
- Not sharing your password with anyone

Thanks,<br>
{{ config('app.name') }}

---

If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:

{{ $resetUrl }}
@endcomponent