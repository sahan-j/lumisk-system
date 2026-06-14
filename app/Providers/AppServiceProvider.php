<?php

namespace App\Providers;

use App\Models\Ticket;
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

        // Open-ticket badge count for the admin sidebar.
        View::composer('components.layouts.admin', function ($view) {
            $count = Schema::hasTable('tickets')
                ? Ticket::where('status', 'open')->count()
                : 0;
            $view->with('openTicketsCount', $count);
        });
    }
}
