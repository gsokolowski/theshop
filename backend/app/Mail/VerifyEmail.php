<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verificationUrl = $this->generateVerificationUrl($user);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
            with: [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
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
     * Generate signed verification URL
     */
    private function generateVerificationUrl(User $user): string
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        
        // Generate signed URL for the backend API route
        $signedUrl = URL::temporarySignedRoute(
            'email.verify',
            now()->addHours(48), // Link expires in 48 hours
            ['id' => $user->id]
        );

        // Extract the query string from the signed URL (preserve exact format)
        $parsedUrl = parse_url($signedUrl);
        $queryString = $parsedUrl['query'] ?? '';

        // Build frontend URL with the exact query string from the signed URL
        // This preserves the parameter order and encoding that Laravel used
        return $frontendUrl . '/email/verify?' . $queryString;
    }
}
