<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\EstimatePdfController;
use App\Http\Controllers\Admin\InvoicePdfController;
use App\Http\Controllers\Admin\TicketAttachmentController;
use App\Livewire\Admin\Clients\ClientShow;
use App\Livewire\Admin\Clients\ClientsIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Estimates\EstimateForm;
use App\Livewire\Admin\Estimates\EstimateShow;
use App\Livewire\Admin\Estimates\EstimatesIndex;
use App\Livewire\Admin\Invoices\InvoiceForm;
use App\Livewire\Admin\Invoices\InvoiceShow;
use App\Livewire\Admin\Invoices\InvoicesIndex;
use App\Livewire\Admin\Projects\ProjectForm;
use App\Livewire\Admin\Projects\ProjectShow;
use App\Livewire\Admin\Projects\ProjectsIndex;
use App\Livewire\Admin\SavedItems\SavedItemsIndex;
use App\Livewire\Admin\Settings\SettingsIndex;
use App\Livewire\Admin\Tickets\TicketShow;
use App\Livewire\Admin\Tickets\TicketsIndex;
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

    // Projects
    Route::get('projects', ProjectsIndex::class)->name('projects.index');
    Route::get('projects/create', ProjectForm::class)->name('projects.create');
    Route::get('projects/{project}/edit', ProjectForm::class)->name('projects.edit');
    Route::get('projects/{project}', ProjectShow::class)->name('projects.show');

    // Support tickets
    Route::get('tickets', TicketsIndex::class)->name('tickets.index');
    Route::get('tickets/{ticket}', TicketShow::class)->name('tickets.show');
    Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('tickets.attachment.download');

    // Saved items
    Route::get('saved-items', SavedItemsIndex::class)->name('saved-items.index');

    // Settings
    Route::get('settings', SettingsIndex::class)->name('settings.index');

    // Profile
    Route::get('profile', [AdminProfileController::class, 'show'])->name('profile');
    Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
});
