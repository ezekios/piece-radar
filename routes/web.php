<?php

use App\Http\Controllers\ClientPartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pieces', [ClientPartController::class, 'index'])
    ->name('client.parts.index');

Route::get('/pieces/{part}/demande', [ClientPartController::class, 'requestForm'])
    ->name('pieces.request');

Route::post('/pieces/{part}/demande', [ClientPartController::class, 'storeRequest'])
    ->name('pieces.request.store');

Route::get('/pieces/{part}', [ClientPartController::class, 'show'])
    ->name('pieces.show');
