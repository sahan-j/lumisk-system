<?php

use App\Http\Controllers\Portal\Auth\LoginController;
use App\Http\Controllers\Portal\EstimatePdfController;
use App\Http\Controllers\Portal\InvoicePdfController;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\Estimates\EstimateShow;
use App\Livewire\Portal\Estimates\EstimatesIndex;
use App\Livewire\Portal\Invoices\InvoiceShow;
use App\Livewire\Portal\Invoices\InvoicesIndex;
use Illuminate\Support\Facades\Route;

/*
| Client portal routes — prefixed with /portal and named portal.*
*/

Route::middleware('client.guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('client.auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Invoices (read-only)
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf');

    // Estimates (with accept/reject)
    Route::get('estimates', EstimatesIndex::class)->name('estimates.index');
    Route::get('estimates/{estimate}', EstimateShow::class)->name('estimates.show');
    Route::get('estimates/{estimate}/pdf', [EstimatePdfController::class, 'download'])->name('estimates.pdf');
});

Route::get('/', function () {
    return redirect()->route(
        auth('client')->check() ? 'portal.dashboard' : 'portal.login'
    );
});
