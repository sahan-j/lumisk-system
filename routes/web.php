<?php

use App\Http\Controllers\PublicViewController;
use App\Livewire\Public\KnowledgeBase\PublicHelpArticle;
use App\Livewire\Public\KnowledgeBase\PublicHelpCategory;
use App\Livewire\Public\KnowledgeBase\PublicHelpCenter;
use Illuminate\Support\Facades\Route;

// Plain redirect (not a closure) so `php artisan route:cache` can serialize it.
Route::redirect('/', '/admin/login');

// Public, no-auth invoice/estimate view (shared via WhatsApp link).
Route::get('/i/{token}', [PublicViewController::class, 'show'])->name('public.view');

// Public knowledge base (no login — only articles with visibility=public).
Route::prefix('help')->name('public.kb.')->group(function () {
    Route::get('/', PublicHelpCenter::class)->name('index');
    Route::get('/category/{category:slug}', PublicHelpCategory::class)->name('category');
    Route::get('/article/{article:slug}', PublicHelpArticle::class)->name('article');
});

// Admin (/admin/*) and Portal (/portal/*) routes are registered from
// routes/admin.php and routes/portal.php via bootstrap/app.php.
