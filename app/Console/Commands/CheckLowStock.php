<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'inventory:check-low-stock';

    protected $description = 'Log an activity for each tracked product at or below its low-stock threshold.';

    public function handle(): int
    {
        $lowStock = Product::where('track_inventory', true)
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->get();

        if ($lowStock->isEmpty()) {
            $this->info('No low stock items.');

            return self::SUCCESS;
        }

        foreach ($lowStock as $product) {
            $qty = rtrim(rtrim(number_format((float) $product->stock_quantity, 2), '0'), '.');
            ActivityLog::log(
                'low_stock_alert',
                "Low stock: {$product->name} ({$qty} {$product->unit} remaining)",
                ['subject_type' => 'Product', 'subject_id' => $product->id, 'subject_label' => $product->sku ?: $product->name]
            );
        }

        $this->info("Found {$lowStock->count()} low stock items.");

        return self::SUCCESS;
    }
}
