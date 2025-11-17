<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class InvoiceGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    public $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return config('invoice.email.enabled', true) ? ['mail'] : [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(str_replace(
                ['{invoice_number}', '{company_name}'],
                [$this->invoice->invoice_number, config('invoice.company.name')],
                config('invoice.email.subject')
            ))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your invoice has been generated.')
            ->line('**Invoice Number:** ' . $this->invoice->invoice_number)
            ->line('**Amount:** ' . $this->invoice->currency . ' ' . number_format($this->invoice->total_amount, 2))
            ->line('**Status:** ' . ucfirst($this->invoice->status));

        // Add due date if available
        if ($this->invoice->due_date) {
            $message->line('**Due Date:** ' . $this->invoice->due_date->format('M d, Y'));
        }

        // Add download link if enabled
        if (config('invoice.email.include_download_link', true)) {
            $downloadUrl = $this->invoice->getSignedDownloadUrl(
                config('invoice.urls.signed_url_expiration', 60)
            );
            $message->action('Download Invoice', $downloadUrl);
        }

        // Attach PDF if enabled and file exists
        if (config('invoice.email.attach_pdf', true) && $this->invoice->pdf_path) {
            $fullPath = Storage::disk(config('invoice.storage.disk'))->path($this->invoice->pdf_path);
            if (file_exists($fullPath)) {
                $message->attach($fullPath, [
                    'as' => $this->invoice->pdf_filename,
                    'mime' => 'application/pdf',
                ]);
            }
        }

        $message->line('Thank you for your business!')
            ->salutation('Best regards, ' . config('invoice.company.name'));

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total_amount,
            'currency' => $this->invoice->currency,
            'status' => $this->invoice->status,
            'due_date' => $this->invoice->due_date?->toDateString(),
        ];
    }
}
