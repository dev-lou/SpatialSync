<?php

use App\Http\AuthenticatedRequest;
use Illuminate\Contracts\Http\Kernel;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

// The middleware and controllers type-hint App\Http\AuthenticatedRequest.
// Without this, Laravel builds a *blank* instance for those type hints while
// the pipeline carries a plain Request: every guarded route then throws a
// TypeError, and controllers read none of the submitted input. Creating the
// request as AuthenticatedRequest and binding it makes one instance serve both.
$request = AuthenticatedRequest::capture();
$app->instance(AuthenticatedRequest::class, $request);

$response = $kernel->handle($request)->send();

$kernel->terminate($request, $response);
