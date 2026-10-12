<?php

declare(strict_types=1);

// 1. Load Composer Autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vioni\Foundation\Application;
use Vioni\Http\Request;

// 2. Bootstrap Vioni Application
/** @var Application $app */
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

// 3. Capture HTTP Request
$request = Request::capture();

// 4. Handle Request & Send Response
$response = $app->handle($request);
$response->send();
