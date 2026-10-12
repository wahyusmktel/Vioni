<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use Vioni\Http\Request;
use Vioni\Routing\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Routes loaded here are typically stateless REST API endpoints.
| All array/object returns are automatically converted to JSON.
*/

Route::get('/api/health', function () {
    return [
        'status' => 'healthy',
        'framework' => 'Vioni Framework',
        'version' => '1.0.0-dev',
        'php' => PHP_VERSION,
        'timestamp' => time(),
    ];
});

Route::get('/api/users/{id}', [HomeController::class, 'profile']);

Route::post('/api/echo', function (Request $request) {
    return [
        'message' => 'Data received safely',
        'payload' => $request->all(),
        'method' => $request->method(),
    ];
});
