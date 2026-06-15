<?php

namespace App\Providers;

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

        // Open-ticket badge count for the admin sidebar.
        View::composer('components.layouts.admin', function ($view) {
            $count = Schema::hasTable('tickets')
                ? Ticket::where('status', 'open')->count()
                : 0;
            $view->with('openTicketsCount', $count);
        });
    }
}
