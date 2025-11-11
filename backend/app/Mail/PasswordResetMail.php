<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;
    public $resetUrl;
    public $requestIp;
    public $expiryMinutes = 60;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $token, string $requestIp)
    {
        $this->user = $user;
        $this->token = $token;
        $this->requestIp = $requestIp;
        $this->resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password-reset',
            with: [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'requestIp' => $this->requestIp,
                'expiryMinutes' => $this->expiryMinutes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message (Laravel 9 compatibility).
     */
    public function build()
    {
        return $this->subject('Reset Your Password - ' . config('app.name'))
                    ->markdown('emails.password-reset')
                    ->with([
                        'user' => $this->user,
                        'resetUrl' => $this->resetUrl,
                        'requestIp' => $this->requestIp,
                        'expiryMinutes' => $this->expiryMinutes,
                    ]);
    }
}