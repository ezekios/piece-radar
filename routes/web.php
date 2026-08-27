<?php

use App\Http\Controllers\ClientPartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pieces', [ClientPartController::class, 'index'])
    ->name('client.parts.index');
