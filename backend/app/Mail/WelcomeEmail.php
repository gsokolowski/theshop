<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $shopUrl;

    /**
     * @param  string  $source  'email_verified' | 'google' — for optional copy tweaks in the view
     */
    public function __construct(
        User $user,
        public string $source = 'email_verified',
    ) {
        $this->user = $user;
        $this->shopUrl = rtrim(config('app.frontend_url'), '/');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to The Shop',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'user' => $this->user,
                'shopUrl' => $this->shopUrl,
                'source' => $this->source,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
