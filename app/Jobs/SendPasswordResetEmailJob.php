<?php

namespace App\Jobs;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPasswordResetEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $resetUrl
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->user->email)) {
            return;
        }

        Mail::to($this->user->email)->send(new ResetPasswordMail($this->user, $this->resetUrl));
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $e): void
    {
        Log::error("Failed to send password reset email to user ID {$this->user->id} ({$this->user->email}): {$e->getMessage()}", [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'exception' => $e,
        ]);
    }
}
