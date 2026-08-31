<?php

use App\Http\Controllers\ClientPartController;
use App\Http\Controllers\ClientRequestController;
use App\Http\Controllers\ScrapyardDashboardController;
use App\Http\Controllers\ScrapyardPartController;
use App\Http\Controllers\ScrapyardRequestController;
use App\Http\Controllers\ScrapyardVehicleController;
use App\Http\Controllers\ScrapyardVehiclePartController;
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

Route::get('/casse/vehicules', [ScrapyardVehicleController::class, 'index'])
    ->name('scrapyard.vehicles.index');

Route::get('/casse/vehicules/ajouter', [ScrapyardVehicleController::class, 'create'])
    ->name('scrapyard.vehicles.create');

Route::post('/casse/vehicules', [ScrapyardVehicleController::class, 'store'])
    ->name('scrapyard.vehicles.store');

Route::get('/casse/vehicules/{vehicle}/pieces/ajouter', [ScrapyardVehiclePartController::class, 'create'])
    ->name('scrapyard.vehicles.parts.create');

Route::post('/casse/vehicules/{vehicle}/pieces', [ScrapyardVehiclePartController::class, 'store'])
    ->name('scrapyard.vehicles.parts.store');

Route::get('/casse/vehicules/{vehicle}/modifier', [ScrapyardVehicleController::class, 'edit'])
    ->name('scrapyard.vehicles.edit');

Route::post('/casse/vehicules/{vehicle}/modifier', [ScrapyardVehicleController::class, 'update'])
    ->name('scrapyard.vehicles.update');

Route::get('/casse/vehicules/{vehicle}', [ScrapyardVehicleController::class, 'show'])
    ->name('scrapyard.vehicles.show');

Route::get('/casse/pieces', [ScrapyardPartController::class, 'index'])
    ->name('scrapyard.parts.index');

Route::get('/casse/pieces/{part}/preparation', [ScrapyardPartController::class, 'preparation'])
    ->name('scrapyard.parts.preparation.edit');

Route::post('/casse/pieces/{part}/preparation', [ScrapyardPartController::class, 'updatePreparation'])
    ->name('scrapyard.parts.preparation.update');

Route::post('/casse/pieces/{part}/statut', [ScrapyardPartController::class, 'updateStatus'])
    ->name('scrapyard.parts.updateStatus');

Route::post('/casse/pieces/{part}/publier', [ScrapyardPartController::class, 'publish'])
    ->name('scrapyard.parts.publish');

Route::post('/casse/pieces/{part}/retirer-publication', [ScrapyardPartController::class, 'unpublish'])
    ->name('scrapyard.parts.unpublish');

Route::get('/casse/pieces/{part}', [ScrapyardPartController::class, 'show'])
    ->name('scrapyard.parts.show');

Route::get('/casse/demandes', [ScrapyardRequestController::class, 'index'])
    ->name('scrapyard.requests.index');

Route::get('/casse/demandes/{partHoldRequest}/acceptation', [ScrapyardRequestController::class, 'confirmAccept'])
    ->name('scrapyard.requests.accept.confirm');

Route::post('/casse/demandes/{partHoldRequest}/accepter', [ScrapyardRequestController::class, 'accept'])
    ->name('scrapyard.requests.accept');

Route::post('/casse/demandes/{partHoldRequest}/refuser', [ScrapyardRequestController::class, 'refuse'])
    ->name('scrapyard.requests.refuse');

Route::post('/casse/demandes/{partHoldRequest}/terminer', [ScrapyardRequestController::class, 'complete'])
    ->name('scrapyard.requests.complete');

Route::post('/casse/demandes/{partHoldRequest}/annuler', [ScrapyardRequestController::class, 'cancel'])
    ->name('scrapyard.requests.cancel');

Route::get('/casse/demandes/{partHoldRequest}', [ScrapyardRequestController::class, 'show'])
    ->name('scrapyard.requests.show');
