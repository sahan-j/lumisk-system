<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Admin (/admin/*) and Portal (/portal/*) routes are registered from
// routes/admin.php and routes/portal.php via bootstrap/app.php.
