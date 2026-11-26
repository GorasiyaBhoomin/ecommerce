<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlertMail;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckLowStockProducts extends Command
{
    protected $signature = 'products:check-low-stock {--threshold=5}';

    protected $description = 'Check low-stock product variants and notify admin';

    public function handle(): int
    {
        $threshold = max(1, (int) $this->option('threshold'));

        $this->info("Checking product stock below {$threshold}...");

        $alerts = collect();
        $variantsToUpdate = collect();

        ProductVariant::with('product')
            ->chunkById(100, function ($variants) use (
                $threshold,
                $alerts,
                $variantsToUpdate
            ) {
                foreach ($variants as $variant) {

                    $stock = (int) $variant->stock_qty;

                    // Reset notification tracking when stock returns to normal.
                    if ($stock >= $threshold) {
                        if ($variant->low_stock_notified_at !== null) {
                            $variant->low_stock_notified_at = null;
                            $variant->low_stock_notification_level = null;
                            $variant->save();
                        }

                        continue;
                    }

                    $level = $stock === 0 ? 'out_of_stock' : 'low_stock';

                    // Skip if this same alert level was already notified.
                    if ($variant->low_stock_notification_level === $level) {
                        continue;
                    }

                    $alerts->push([
                        'variant_id' => $variant->id,
                        'product_name' => $variant->product->name ?? 'Unknown Product',
                        'variant_name' => $variant->name ?? 'Variant #'.$variant->id,
                        'sku' => $variant->sku ?? 'N/A',
                        'stock_qty' => $stock,
                        'status' => $level === 'out_of_stock'
                            ? 'Out of Stock'
                            : 'Low Stock',
                    ]);

                    $variantsToUpdate->push($variant);
                }
            });

        if ($alerts->isEmpty()) {
            $this->info('No new low-stock alerts found.');

            return self::SUCCESS;
        }

        try {
            // Send one summary email containing all new alerts.
            Mail::to(config('mail.admin_address'))
                ->send(new LowStockAlertMail($alerts));

            // Mark notifications only after successful email sending.
            foreach ($variantsToUpdate as $variant) {
                $variant->low_stock_notified_at = now();
                $variant->low_stock_notification_level =
                    (int) $variant->stock_qty === 0
                        ? 'out_of_stock'
                        : 'low_stock';

                $variant->save();
            }

            Log::info('Low stock alert email sent.', [
                'total_alerts' => $alerts->count(),
            ]);

            $this->info("Successfully sent {$alerts->count()} stock alert(s).");

            return self::SUCCESS;

        } catch (\Throwable $e) {

            Log::error('Low stock email failed.', [
                'error' => $e->getMessage(),
            ]);

            $this->error('Email failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
