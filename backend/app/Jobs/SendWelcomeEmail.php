<?php

namespace App\Jobs;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Runs synchronously when dispatched (no ShouldQueue) so the welcome message is sent in the same
 * request as verification / first Google signup. Queued jobs would otherwise require a running
 * worker when QUEUE_CONNECTION=database.
 *
 * Retries with backoff: providers like Mailtrap (free tier) return 550 "Too many emails per second"
 * when verification and welcome send back-to-back.
 */
class SendWelcomeEmail
{
    use Queueable, SerializesModels;

    private const MAIL_RETRY_TIMES = 5;

    private const MAIL_RETRY_SLEEP_MS = 2500;

    public function __construct(
        public User $user,
        public string $source = 'email_verified',
    ) {}

    public function handle(): void
    {
        retry(
            self::MAIL_RETRY_TIMES,
            fn () => Mail::to($this->user->email)->send(new WelcomeEmail($this->user, $this->source)),
            self::MAIL_RETRY_SLEEP_MS
        );
    }
}
