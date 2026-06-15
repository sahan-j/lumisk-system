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
use App\Livewire\Admin\Expenses\ExpenseForm;
use App\Livewire\Admin\Expenses\ExpensesIndex;
use App\Livewire\Admin\Invoices\InvoiceForm;
use App\Livewire\Admin\Invoices\InvoiceShow;
use App\Livewire\Admin\Invoices\InvoicesIndex;
use App\Livewire\Admin\Projects\ProjectForm;
use App\Livewire\Admin\Projects\ProjectShow;
use App\Livewire\Admin\Projects\ProjectsIndex;
use App\Livewire\Admin\Reports\ClientReport;
use App\Livewire\Admin\Reports\ExpenseReport;
use App\Livewire\Admin\Reports\InvoiceAgingReport;
use App\Livewire\Admin\Reports\ProfitLossReport;
use App\Livewire\Admin\Reports\ProjectReport;
use App\Livewire\Admin\Reports\ReportsIndex;
use App\Livewire\Admin\Reports\RevenueReport;
use App\Livewire\Admin\Reports\TaxReport;
use App\Livewire\Admin\SavedItems\SavedItemsIndex;
use App\Livewire\Admin\Settings\SettingsIndex;
use App\Livewire\Admin\Staff\StaffForm;
use App\Livewire\Admin\Staff\StaffIndex;
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

    Route::get('dashboard', Dashboard::class)->name('dashboard')->middleware('permission:dashboard.view');

    // Clients
    Route::get('clients', ClientsIndex::class)->name('clients.index')->middleware('permission:clients.view');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show')->middleware('permission:clients.view');

    // Invoices
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index')->middleware('permission:invoices.view');
    Route::get('invoices/create', InvoiceForm::class)->name('invoices.create')->middleware('permission:invoices.create');
    Route::get('invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit')->middleware('permission:invoices.edit');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show')->middleware('permission:invoices.view');
    Route::get('invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf')->middleware('permission:invoices.view');

    // Estimates
    Route::get('estimates', EstimatesIndex::class)->name('estimates.index')->middleware('permission:estimates.view');
    Route::get('estimates/create', EstimateForm::class)->name('estimates.create')->middleware('permission:estimates.create');
    Route::get('estimates/{estimate}/edit', EstimateForm::class)->name('estimates.edit')->middleware('permission:estimates.edit');
    Route::get('estimates/{estimate}', EstimateShow::class)->name('estimates.show')->middleware('permission:estimates.view');
    Route::get('estimates/{estimate}/pdf', [EstimatePdfController::class, 'download'])->name('estimates.pdf')->middleware('permission:estimates.view');

    // Projects
    Route::get('projects', ProjectsIndex::class)->name('projects.index')->middleware('permission:projects.view');
    Route::get('projects/create', ProjectForm::class)->name('projects.create')->middleware('permission:projects.create');
    Route::get('projects/{project}/edit', ProjectForm::class)->name('projects.edit')->middleware('permission:projects.edit');
    Route::get('projects/{project}', ProjectShow::class)->name('projects.show')->middleware('permission:projects.view');

    // Expenses
    Route::get('expenses', ExpensesIndex::class)->name('expenses.index')->middleware('permission:expenses.view');
    Route::get('expenses/create', ExpenseForm::class)->name('expenses.create')->middleware('permission:expenses.create');
    Route::get('expenses/{expense}/edit', ExpenseForm::class)->name('expenses.edit')->middleware('permission:expenses.edit');

    // Reports & analytics
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports', ReportsIndex::class)->name('reports.index');
        Route::get('reports/revenue', RevenueReport::class)->name('reports.revenue');
        Route::get('reports/expenses', ExpenseReport::class)->name('reports.expenses');
        Route::get('reports/profit-loss', ProfitLossReport::class)->name('reports.profit-loss');
        Route::get('reports/clients', ClientReport::class)->name('reports.clients');
        Route::get('reports/invoice-aging', InvoiceAgingReport::class)->name('reports.invoice-aging');
        Route::get('reports/tax', TaxReport::class)->name('reports.tax');
        Route::get('reports/projects', ProjectReport::class)->name('reports.projects');
    });

    // Support tickets
    Route::get('tickets', TicketsIndex::class)->name('tickets.index')->middleware('permission:tickets.view');
    Route::get('tickets/{ticket}', TicketShow::class)->name('tickets.show')->middleware('permission:tickets.view');
    Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('tickets.attachment.download')->middleware('permission:tickets.view');

    // Saved items
    Route::get('saved-items', SavedItemsIndex::class)->name('saved-items.index');

    // Settings
    Route::get('settings', SettingsIndex::class)->name('settings.index')->middleware('permission:settings.view');

    // Staff & roles
    Route::get('staff', StaffIndex::class)->name('staff.index')->middleware('permission:staff.view');
    Route::get('staff/create', StaffForm::class)->name('staff.create')->middleware('permission:staff.create');
    Route::get('staff/{user}/edit', StaffForm::class)->name('staff.edit')->middleware('permission:staff.edit');

    // Profile
    Route::get('profile', [AdminProfileController::class, 'show'])->name('profile');
    Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
});
