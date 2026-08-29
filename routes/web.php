<?php

use App\Http\Controllers\ClientPartController;
use App\Http\Controllers\ClientRequestController;
use App\Http\Controllers\ScrapyardDashboardController;
use App\Http\Controllers\ScrapyardPartController;
use App\Http\Controllers\ScrapyardRequestController;
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

Route::get('/mes-demandes', [ClientRequestController::class, 'index'])
    ->name('client.requests.index');

Route::get('/mes-demandes/{partHoldRequest}', [ClientRequestController::class, 'show'])
    ->name('client.requests.show');

Route::get('/casse', [ScrapyardDashboardController::class, 'index'])
    ->name('scrapyard.dashboard');

Route::get('/casse/pieces', [ScrapyardPartController::class, 'index'])
    ->name('scrapyard.parts.index');

Route::get('/casse/pieces/{part}', [ScrapyardPartController::class, 'show'])
    ->name('scrapyard.parts.show');

Route::get('/casse/demandes', [ScrapyardRequestController::class, 'index'])
    ->name('scrapyard.requests.index');

Route::post('/casse/demandes/{partHoldRequest}/accepter', [ScrapyardRequestController::class, 'accept'])
    ->name('scrapyard.requests.accept');

Route::post('/casse/demandes/{partHoldRequest}/refuser', [ScrapyardRequestController::class, 'refuse'])
    ->name('scrapyard.requests.refuse');

Route::get('/casse/demandes/{partHoldRequest}', [ScrapyardRequestController::class, 'show'])
    ->name('scrapyard.requests.show');
