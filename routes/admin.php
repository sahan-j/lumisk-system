<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\EstimatePdfController;
use App\Http\Controllers\Admin\InvoicePdfController;
use App\Livewire\Admin\Clients\ClientShow;
use App\Livewire\Admin\Clients\ClientsIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Estimates\EstimateForm;
use App\Livewire\Admin\Estimates\EstimateShow;
use App\Livewire\Admin\Estimates\EstimatesIndex;
use App\Livewire\Admin\Invoices\InvoiceForm;
use App\Livewire\Admin\Invoices\InvoiceShow;
use App\Livewire\Admin\Invoices\InvoicesIndex;
use App\Livewire\Admin\SavedItems\SavedItemsIndex;
use App\Livewire\Admin\Settings\SettingsIndex;
use Illuminate\Support\Facades\Route;

/*
| Admin routes — prefixed with /admin and named admin.*
*/

Route::middleware('admin.guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('admin.auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Clients
    Route::get('clients', ClientsIndex::class)->name('clients.index');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show');

    // Invoices
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('invoices/create', InvoiceForm::class)->name('invoices.create');
    Route::get('invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf');

    // Estimates
    Route::get('estimates', EstimatesIndex::class)->name('estimates.index');
    Route::get('estimates/create', EstimateForm::class)->name('estimates.create');
    Route::get('estimates/{estimate}/edit', EstimateForm::class)->name('estimates.edit');
    Route::get('estimates/{estimate}', EstimateShow::class)->name('estimates.show');
    Route::get('estimates/{estimate}/pdf', [EstimatePdfController::class, 'download'])->name('estimates.pdf');

    // Saved items
    Route::get('saved-items', SavedItemsIndex::class)->name('saved-items.index');

    // Settings
    Route::get('settings', SettingsIndex::class)->name('settings.index');
});
