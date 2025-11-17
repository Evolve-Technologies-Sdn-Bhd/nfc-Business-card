<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminBulkOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $businessAccount = $this->data['business_account'];
        $orderDetails = $this->data['order_details'];

        return (new MailMessage)
            ->subject('🚨 New Bulk NFC Card Order - Action Required')
            ->greeting('Hello Super Admin!')
            ->line("**{$businessAccount['company']}** has placed a bulk order for NFC cards.")
            ->line("**Business Account:** {$businessAccount['name']} ({$businessAccount['email']})")
            ->line("**Number of Employees:** {$orderDetails['employee_count']}")
            ->line("**Design Method:** " . ucfirst($orderDetails['design_method']))
            ->line("**Order Date:** {$orderDetails['order_date']}")
            ->action('View Order Details', url('/admin/orders'))
            ->line('Please review and approve this order as soon as possible.')
            ->line('The employee data file has been attached for your review.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->data['type'],
            'priority' => $this->data['priority'],
            'pinned' => $this->data['pinned'],
            'title' => '🚨 New Bulk NFC Card Order',
            'message' => "{$this->data['business_account']['company']} placed a bulk order for {$this->data['order_details']['employee_count']} employee cards.",
            'business_account' => $this->data['business_account'],
            'order_details' => $this->data['order_details'],
            'file_path' => $this->data['file_path'],
            'cards' => $this->data['cards'],
            'action_url' => '/admin/orders',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
