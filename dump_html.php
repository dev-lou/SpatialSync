<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$request = Request::create('/', 'GET');
$response = $kernel->handle($request);
echo '<pre>'.htmlspecialchars($response->getContent()).'</pre>';
