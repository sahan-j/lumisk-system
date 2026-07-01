<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CreditNotePdfController;
use App\Http\Controllers\Admin\EstimatePdfController;
use App\Http\Controllers\Admin\InvoicePdfController;
use App\Http\Controllers\Admin\TicketAttachmentController;
use App\Livewire\Admin\AuditLog\AuditLogIndex;
use App\Livewire\Admin\AuditLog\AuditLogShow;
use App\Livewire\Admin\Backups\BackupsIndex;
use App\Livewire\Admin\Clients\ClientShow;
use App\Livewire\Admin\Clients\ClientsIndex;
use App\Livewire\Admin\CreditNotes\CreditNoteForm;
use App\Livewire\Admin\CreditNotes\CreditNoteShow;
use App\Livewire\Admin\CreditNotes\CreditNotesIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Estimates\EstimateForm;
use App\Livewire\Admin\Estimates\EstimateShow;
use App\Livewire\Admin\Estimates\EstimatesIndex;
use App\Livewire\Admin\Expenses\ExpenseForm;
use App\Livewire\Admin\Expenses\ExpensesIndex;
use App\Livewire\Admin\KnowledgeBase\KbArticleForm;
use App\Livewire\Admin\KnowledgeBase\KbArticles;
use App\Livewire\Admin\KnowledgeBase\KbIndex;
use App\Livewire\Admin\Invoices\InvoiceForm;
use App\Livewire\Admin\Invoices\InvoiceShow;
use App\Livewire\Admin\Invoices\InvoicesIndex;
use App\Livewire\Admin\Leads\LeadForm;
use App\Livewire\Admin\Leads\LeadShow;
use App\Livewire\Admin\Pipeline\PipelineBoard;
use App\Livewire\Admin\Products\ProductForm;
use App\Livewire\Admin\Products\ProductShow;
use App\Livewire\Admin\Products\ProductsIndex;
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
use App\Livewire\Admin\Subscriptions\PlansIndex as SubscriptionPlansIndex;
use App\Livewire\Admin\Subscriptions\SubscriptionForm;
use App\Livewire\Admin\Subscriptions\SubscriptionShow;
use App\Livewire\Admin\Subscriptions\SubscriptionsIndex;
use App\Livewire\Admin\Templates\TemplateForm;
use App\Livewire\Admin\Templates\TemplatesIndex;
use App\Livewire\Admin\TimeTracking\TimeReport;
use App\Livewire\Admin\TimeTracking\TimeTrackingIndex;
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
    Route::get('clients/{client}/documents', \App\Livewire\Admin\Clients\ClientDocuments::class)->name('clients.documents')->middleware('permission:clients.view');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show')->middleware('permission:clients.view');

    // Documents (overview + secure download)
    Route::get('documents', \App\Livewire\Admin\Documents\DocumentsIndex::class)->name('documents.index')->middleware('permission:clients.view');
    Route::get('documents/{document}/download', [\App\Http\Controllers\Admin\ClientDocumentController::class, 'download'])->name('documents.download')->middleware('permission:clients.view');

    // CRM sales pipeline (kanban board + leads)
    Route::get('pipeline', PipelineBoard::class)->name('pipeline.index')->middleware('permission:pipeline.view');
    Route::get('leads/create', LeadForm::class)->name('leads.create')->middleware('permission:leads.create');
    Route::get('leads/{lead}/edit', LeadForm::class)->name('leads.edit')->middleware('permission:leads.edit');
    Route::get('leads/{lead}', LeadShow::class)->name('leads.show')->middleware('permission:pipeline.view');

    // Invoices
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index')->middleware('permission:invoices.view');
    Route::get('invoices/create', InvoiceForm::class)->name('invoices.create')->middleware('permission:invoices.create');
    Route::get('invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit')->middleware('permission:invoices.edit');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show')->middleware('permission:invoices.view');
    Route::get('invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf')->middleware('permission:invoices.view');

    // Credit notes & refunds
    Route::get('credit-notes', CreditNotesIndex::class)->name('credit-notes.index')->middleware('permission:credit-notes.view');
    Route::get('credit-notes/create', CreditNoteForm::class)->name('credit-notes.create')->middleware('permission:credit-notes.create');
    Route::get('credit-notes/{creditNote}/edit', CreditNoteForm::class)->name('credit-notes.edit')->middleware('permission:credit-notes.edit');
    Route::get('credit-notes/{creditNote}', CreditNoteShow::class)->name('credit-notes.show')->middleware('permission:credit-notes.view');
    Route::get('credit-notes/{creditNote}/pdf', [CreditNotePdfController::class, 'download'])->name('credit-notes.pdf')->middleware('permission:credit-notes.view');
    // Shortcut: create a credit note pre-filled from an invoice.
    Route::get('invoices/{invoice}/credit-note', fn (\App\Models\Invoice $invoice) => redirect()->route('admin.credit-notes.create', ['invoice' => $invoice->id]))
        ->name('invoices.credit-note')->middleware('permission:credit-notes.create');

    // Quote requests (client-submitted → convert to estimate)
    Route::get('quote-requests', [\App\Http\Controllers\Admin\AdminQuoteRequestController::class, 'index'])->name('quote-requests.index')->middleware('permission:estimates.view');
    Route::get('quote-requests/{quoteRequest}', [\App\Http\Controllers\Admin\AdminQuoteRequestController::class, 'show'])->name('quote-requests.show')->middleware('permission:estimates.view');
    Route::post('quote-requests/{quoteRequest}/convert', [\App\Http\Controllers\Admin\AdminQuoteRequestController::class, 'convertToEstimate'])->name('quote-requests.convert')->middleware('permission:estimates.create');
    Route::post('quote-requests/{quoteRequest}/decline', [\App\Http\Controllers\Admin\AdminQuoteRequestController::class, 'decline'])->name('quote-requests.decline')->middleware('permission:estimates.edit');
    Route::get('quote-requests/{quoteRequest}/attachments/{index}/download', [\App\Http\Controllers\Admin\AdminQuoteRequestController::class, 'downloadAttachment'])->name('quote-requests.attachment.download')->middleware('permission:estimates.view');

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

    // Time tracking & timesheets
    Route::get('time', TimeTrackingIndex::class)->name('time.index')->middleware('permission:projects.view');
    Route::get('time/report', TimeReport::class)->name('time.report')->middleware('permission:reports.view');

    // Expenses
    Route::get('expenses', ExpensesIndex::class)->name('expenses.index')->middleware('permission:expenses.view');
    Route::get('expenses/create', ExpenseForm::class)->name('expenses.create')->middleware('permission:expenses.create');
    Route::get('expenses/{expense}/edit', ExpenseForm::class)->name('expenses.edit')->middleware('permission:expenses.edit');

    // Subscriptions & retainer billing
    Route::get('subscription-plans', SubscriptionPlansIndex::class)->name('subscription-plans.index')->middleware('permission:subscriptions.manage_plans');
    Route::get('subscriptions', SubscriptionsIndex::class)->name('subscriptions.index')->middleware('permission:subscriptions.view');
    Route::get('subscriptions/create', SubscriptionForm::class)->name('subscriptions.create')->middleware('permission:subscriptions.create');
    Route::get('subscriptions/{subscription}/edit', SubscriptionForm::class)->name('subscriptions.edit')->middleware('permission:subscriptions.edit');
    Route::get('subscriptions/{subscription}', SubscriptionShow::class)->name('subscriptions.show')->middleware('permission:subscriptions.view');

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

    // Products & inventory
    Route::get('products', ProductsIndex::class)->name('products.index')->middleware('permission:products.view');
    Route::get('products/create', ProductForm::class)->name('products.create')->middleware('permission:products.create');
    Route::get('products/{product}/edit', ProductForm::class)->name('products.edit')->middleware('permission:products.edit');
    Route::get('products/{product}', ProductShow::class)->name('products.show')->middleware('permission:products.view');

    // Saved items
    Route::get('saved-items', SavedItemsIndex::class)->name('saved-items.index');

    // Invoice templates (reusable presets)
    Route::get('invoice-templates', TemplatesIndex::class)->name('invoice-templates.index')->middleware('permission:invoices.view');
    Route::get('invoice-templates/create', TemplateForm::class)->name('invoice-templates.create')->middleware('permission:invoices.create');
    Route::get('invoice-templates/{template}/edit', TemplateForm::class)->name('invoice-templates.edit')->middleware('permission:invoices.edit');

    // Settings
    Route::get('settings', SettingsIndex::class)->name('settings.index')->middleware('permission:settings.view');

    // Staff & roles
    Route::get('staff', StaffIndex::class)->name('staff.index')->middleware('permission:staff.view');
    Route::get('staff/create', StaffForm::class)->name('staff.create')->middleware('permission:staff.create');
    Route::get('staff/{user}/edit', StaffForm::class)->name('staff.edit')->middleware('permission:staff.edit');

    // Audit log (data-change history)
    Route::get('audit-log', AuditLogIndex::class)->name('audit-log.index')->middleware('permission:settings.view');
    Route::get('audit-log/{auditLog}', AuditLogShow::class)->name('audit-log.show')->middleware('permission:settings.view');

    // Database backups
    Route::get('backups', BackupsIndex::class)->name('backups.index')->middleware('permission:settings.view');
    Route::get('backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download')->middleware('permission:settings.view');

    // Knowledge base (help articles)
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('knowledge-base', KbIndex::class)->name('kb.index');
        Route::get('knowledge-base/articles', KbArticles::class)->name('kb.articles.index');
        Route::get('knowledge-base/articles/create', KbArticleForm::class)->name('kb.articles.create');
        Route::get('knowledge-base/articles/{article}/edit', KbArticleForm::class)->name('kb.articles.edit');
    });

    // Notifications
    Route::get('notifications', \App\Livewire\Admin\NotificationsIndex::class)->name('notifications.index');

    // Profile
    Route::get('profile', [AdminProfileController::class, 'show'])->name('profile');
    Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
});
