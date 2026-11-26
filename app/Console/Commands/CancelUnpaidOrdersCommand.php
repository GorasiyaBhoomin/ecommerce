<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid {--minutes=30 : Age threshold in minutes for unpaid orders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel unpaid orders older than 30 minutes and restore variant stock';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        if ($minutes < 0) {
            $minutes = 30;
        }

        $cutoff = now()->subMinutes($minutes);

        $this->info("Checking for unpaid orders older than {$minutes} minutes (created before {$cutoff->toDateTimeString()})...");

        // Find orders whose payment is pending, not yet cancelled, and older than the cutoff threshold.
        $orders = Order::with('items')
            ->whereRaw('LOWER(TRIM(payment_status)) = ?', ['pending'])
            ->whereRaw('LOWER(TRIM(order_status)) != ?', ['cancelled'])
            ->where('created_at', '<=', $cutoff)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No unpaid expired orders found.');

            return self::SUCCESS;
        }

        $this->info("Found {$orders->count()} unpaid order(s) eligible for cancellation.");

        $cancelledCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            try {
                $wasCancelled = DB::transaction(function () use ($order): bool {
                    /** @var Order|null $lockedOrder */
                    $lockedOrder = Order::with('items')->where('id', $order->id)->lockForUpdate()->first();

                    if (! $lockedOrder) {
                        return false;
                    }

                    $orderStatus = strtolower(trim((string) $lockedOrder->order_status));
                    $paymentStatus = strtolower(trim((string) $lockedOrder->payment_status));

                    // Guard 1: Do not restore stock more than once if already cancelled
                    if ($orderStatus === 'cancelled') {
                        return false;
                    }

                    // Guard 2: Do not cancel successfully paid or settled orders
                    if ($paymentStatus !== 'pending') {
                        return false;
                    }

                    // Restore purchased product quantities to variant stock
                    foreach ($lockedOrder->items as $item) {
                        if ($item->variant_id) {
                            $variant = ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first();
                            if ($variant) {
                                $variant->increment('stock_qty', (int) $item->quantity);
                            }
                        }
                    }

                    // Update order and payment status
                    $lockedOrder->order_status = 'cancelled';
                    $lockedOrder->payment_status = 'cancelled';
                    $lockedOrder->save();

                    return true;
                });

                if ($wasCancelled) {
                    $cancellationTime = now()->toDateTimeString();
                    $cancelledCount++;

                    // Requirement 9: Log every automatically cancelled order with its order ID and cancellation time
                    Log::info("Order #{$order->id} automatically cancelled at {$cancellationTime}. Unpaid timeout exceeded {$minutes} minutes.", [
                        'order_id' => $order->id,
                        'cancellation_time' => $cancellationTime,
                        'created_at' => (string) $order->created_at,
                        'payment_status' => 'pending',
                        'order_status' => 'cancelled',
                        'items_restored' => $order->items->count(),
                    ]);

                    $this->line("<info>✓</info> Cancelled Order #{$order->id} and restored stock at {$cancellationTime}.");
                } else {
                    $skippedCount++;
                    $this->line("<comment>-</comment> Skipped Order #{$order->id} (already cancelled or paid).");
                }
            } catch (\Throwable $e) {
                $failedCount++;

                Log::error("Failed to automatically cancel unpaid order #{$order->id}: {$e->getMessage()}", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->error("Failed to cancel Order #{$order->id}: {$e->getMessage()}");
            }
        }

        $this->info("Completed. Cancelled: {$cancelledCount}, Skipped: {$skippedCount}, Failed: {$failedCount}.");

        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
