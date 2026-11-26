<?php

namespace App\Jobs;

use App\Mail\orderMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendOrderEmailJob implements ShouldQueue
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
        public Order $order
    ) {
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = $this->order->loadMissing(['user', 'items.product']);

        if (empty($order->user?->email)) {
            Log::warning("Cannot send queued order email: Customer email is missing for order ID {$order->id}");
            $order->update([
                'email_status' => 'failed',
                'email_error' => 'Customer email address is missing.',
            ]);

            return;
        }

        Mail::to($order->user->email)->send(new orderMail($order));

        $order->update([
            'email_status' => 'sent',
            'email_sent_at' => now(),
            'email_error' => null,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $e): void
    {
        Log::error("Failed to send queued order email for order ID {$this->order->id}: {$e->getMessage()}", [
            'order_id' => $this->order->id,
            'exception' => $e,
        ]);

        $this->order->update([
            'email_status' => 'failed',
            'email_error' => Str::limit($e->getMessage(), 255),
        ]);

        $this->order->update([
            'email_status' => 'failed',
            'email_error' => Str::limit($e->getMessage(), 255),
        ]);
    }
}
