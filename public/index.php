<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// PHP 8.5 deprecates the PDO::MYSQL_ATTR_SSL_CA constant. Laravel's bundled
// base config (vendor/laravel/framework/config/database.php) still references
// it while merging defaults during bootstrap — before the framework installs
// its own error handler — so it surfaces as a cosmetic deprecation notice.
// Suppress E_DEPRECATED for the boot phase only; Laravel restores full error
// reporting via the HandleExceptions bootstrapper immediately afterwards.
error_reporting(error_reporting() & ~E_DEPRECATED);

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
