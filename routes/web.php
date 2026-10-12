<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use Vioni\Routing\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Routes loaded here are for the presentation layer of the application.
*/

Route::get('/', [HomeController::class, 'index']);

Route::get('/hello/{name}', function (string $name) {
    return "<h1>Halo {$name}! Selamat datang di Vioni Framework.</h1>";
});
