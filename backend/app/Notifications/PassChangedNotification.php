<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Has Been Changed - ' . config('app.name'))
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . '!')
            ->line('This is a confirmation that your password has been successfully changed.')
            ->line('**If you did not make this change**, please contact our support team immediately at ' . config('mail.support_email', 'support@' . config('app.domain')))
            ->line('**Security Tips:**')
            ->line('• Never share your password with anyone')
            ->line('• Enable two-factor authentication for extra security')
            ->line('• Use a unique password for each online account')
            ->line('• Consider using a password manager')
            ->salutation('Stay safe, The ' . config('app.name') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'changed_at' => now()->toIso8601String(),
        ];
    }
}