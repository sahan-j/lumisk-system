<?php

use App\Http\Controllers\Portal\Auth\LoginController;
use App\Http\Controllers\Portal\Auth\PortalPasswordResetController;
use App\Http\Controllers\Portal\CreditNotePdfController;
use App\Http\Controllers\Portal\EstimatePdfController;
use App\Http\Controllers\Portal\InvoicePdfController;
use App\Http\Controllers\Portal\PortalProfileController;
use App\Http\Controllers\Portal\TicketAttachmentController;
use App\Livewire\Portal\CreditNotes\CreditNotesIndex as PortalCreditNotesIndex;
use App\Livewire\Portal\CreditNotes\CreditNoteShow as PortalCreditNoteShow;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\Estimates\EstimateShow;
use App\Livewire\Portal\Estimates\EstimatesIndex;
use App\Livewire\Portal\Invoices\InvoiceShow;
use App\Livewire\Portal\Invoices\InvoicesIndex;
use App\Livewire\Portal\KnowledgeBase\HelpArticle;
use App\Livewire\Portal\KnowledgeBase\HelpCategory;
use App\Livewire\Portal\KnowledgeBase\HelpCenter;
use App\Livewire\Portal\Projects\ProjectShow;
use App\Livewire\Portal\Projects\ProjectsIndex;
use App\Livewire\Portal\Subscriptions\SubscriptionShow as PortalSubscriptionShow;
use App\Livewire\Portal\Subscriptions\SubscriptionsIndex as PortalSubscriptionsIndex;
use App\Livewire\Portal\Tickets\TicketCreate;
use App\Livewire\Portal\Tickets\TicketShow;
use App\Livewire\Portal\Tickets\TicketsIndex;
use Illuminate\Support\Facades\Route;

/*
| Client portal routes — prefixed with /portal and named portal.*
*/

Route::middleware('client.guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    // Password reset
    Route::get('forgot-password', [PortalPasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('forgot-password', [PortalPasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [PortalPasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [PortalPasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('client.auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Invoices (read-only)
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf');

    // Credit notes (read-only)
    Route::get('credit-notes', PortalCreditNotesIndex::class)->name('credit-notes.index');
    Route::get('credit-notes/{creditNote}', PortalCreditNoteShow::class)->name('credit-notes.show');
    Route::get('credit-notes/{creditNote}/pdf', [CreditNotePdfController::class, 'download'])->name('credit-notes.pdf');

    // Estimates (with accept/reject)
    Route::get('estimates', EstimatesIndex::class)->name('estimates.index');
    Route::get('estimates/{estimate}', EstimateShow::class)->name('estimates.show');
    Route::get('estimates/{estimate}/pdf', [EstimatePdfController::class, 'download'])->name('estimates.pdf');

    // Projects (read-only)
    Route::get('projects', ProjectsIndex::class)->name('projects.index');
    Route::get('projects/{project}', ProjectShow::class)->name('projects.show');

    // Subscriptions (read-only + cancellation requests)
    Route::get('subscriptions', PortalSubscriptionsIndex::class)->name('subscriptions.index');
    Route::get('subscriptions/{subscription}', PortalSubscriptionShow::class)->name('subscriptions.show');

    // Support tickets
    Route::get('tickets', TicketsIndex::class)->name('tickets.index');
    Route::get('tickets/create', TicketCreate::class)->name('tickets.create');
    Route::get('tickets/{ticket}', TicketShow::class)->name('tickets.show');
    Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('tickets.attachment.download');

    // Help center / knowledge base
    Route::get('help', HelpCenter::class)->name('kb.index');
    Route::get('help/category/{category:slug}', HelpCategory::class)->name('kb.category');
    Route::get('help/article/{article:slug}', HelpArticle::class)->name('kb.article');

    // Profile
    Route::get('profile', [PortalProfileController::class, 'show'])->name('profile');
    Route::put('profile', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [PortalProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::get('/', function () {
    return redirect()->route(
        auth('client')->check() ? 'portal.dashboard' : 'portal.login'
    );
});
