<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// OOM/fatal yakalayıcıyı framework'ten ÖNCE kaydet ki bizim shutdown
// handler'ımız Laravel'in handler'ından önce çalışsın ve hangi URL'in kaç
// MB'da patladığını storage/logs/fatal.log'a yazsın. Oku: scripts/diag.sh fatal
\App\Support\FatalLogger::register(__DIR__.'/../storage/logs/fatal.log');

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
