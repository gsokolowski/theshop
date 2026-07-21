<?php

namespace App\Jobs;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Queued welcome email after verification or first Google signup.
 * Requires a running queue worker when QUEUE_CONNECTION is redis/database.
 *
 * Retries with backoff: providers like Mailtrap (free tier) return 550 "Too many emails per second"
 * when verification and welcome send back-to-back.
 */
class SendWelcomeEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

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
