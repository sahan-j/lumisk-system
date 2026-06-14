<?php

use App\Http\Controllers\PublicViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Public, no-auth invoice/estimate view (shared via WhatsApp link).
Route::get('/i/{token}', [PublicViewController::class, 'show'])->name('public.view');

// Admin (/admin/*) and Portal (/portal/*) routes are registered from
// routes/admin.php and routes/portal.php via bootstrap/app.php.
