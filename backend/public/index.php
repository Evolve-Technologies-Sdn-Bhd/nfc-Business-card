<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// ⚡ FIRST — suppress ALL PHP notices/warnings/errors from leaking into JSON response body
// (common Windows/XAMPP issue: "PHP Request Startup: file created in the system's temporary directory"
//  gets prepended to any upload/JSON response → malformed JSON, axios can't parse to object)
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_STRICT);

// ⚡ Clean any accidental startup output BEFORE kernel handles request
// (handles "file created in tmp dir" + session auto-start headers + UTF-8 BOM leaks)
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

// ⚡ Just to be safe: discard any stray output buffer before kernel terminates
// (prevents trailing whitespace from vendor/ includes from corrupting JSON)
while (ob_get_level() > 0) {
    $out = ob_end_clean();
}
