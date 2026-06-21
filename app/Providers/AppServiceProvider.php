<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\Ticket;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // @permission('invoices.create') ... @endpermission
        Blade::if('permission', function (string $permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // Open-ticket + past-due-subscription badge counts for the admin sidebar.
        View::composer('components.layouts.admin', function ($view) {
            $openTickets = Schema::hasTable('tickets')
                ? Ticket::where('status', 'open')->count()
                : 0;
            $pastDue = Schema::hasTable('subscriptions')
                ? Subscription::where('status', 'past_due')->count()
                : 0;
            $lowStock = Schema::hasTable('products')
                ? Product::where('track_inventory', true)
                    ->whereNotNull('low_stock_threshold')
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('is_active', true)->count()
                : 0;
            $view->with('openTicketsCount', $openTickets);
            $view->with('pastDueCount', $pastDue);
            $view->with('lowStockCount', $lowStock);
        });
    }
}
