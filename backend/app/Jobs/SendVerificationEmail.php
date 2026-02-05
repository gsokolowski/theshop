<?php

namespace App\Jobs;

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Only send verification email if email is not already verified
        if (!$this->user->email_verified_at) {
            Mail::to($this->user->email)->send(new VerifyEmail($this->user));
        }
    }
}
