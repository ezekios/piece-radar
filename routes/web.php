<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\ClientPartController;
use App\Http\Controllers\ClientRequestController;
use App\Http\Controllers\EmailVerificationNotificationController;
use App\Http\Controllers\EmailVerificationPromptController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\PasswordResetLinkController;
use App\Http\Controllers\RegisteredClientController;
use App\Http\Controllers\ScrapyardDashboardController;
use App\Http\Controllers\ScrapyardPartController;
use App\Http\Controllers\ScrapyardRequestController;
use App\Http\Controllers\ScrapyardVehicleController;
use App\Http\Controllers\ScrapyardVehiclePartController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)
    ->name('home');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1']);

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/mot-de-passe-oublie', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/mot-de-passe-oublie', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reinitialiser-mot-de-passe/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reinitialiser-mot-de-passe', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/inscription', [RegisteredClientController::class, 'create'])
    ->middleware('guest')
    ->name('client.register.create');

Route::post('/inscription', [RegisteredClientController::class, 'store'])
    ->middleware('guest')
    ->name('client.register.store');

Route::get('/verification-email', EmailVerificationPromptController::class)
    ->middleware(['auth', 'client'])
    ->name('verification.notice');

Route::get('/verification-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'client', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/verification-email/renvoyer', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'client', 'throttle:6,1'])
    ->name('verification.send');

Route::get('/pieces', [ClientPartController::class, 'index'])
    ->name('client.parts.index');

Route::middleware(['auth', 'client', 'verified'])->group(function (): void {
    Route::get('/pieces/{part}/demande', [ClientPartController::class, 'requestForm'])
        ->name('pieces.request');

    Route::post('/pieces/{part}/demande', [ClientPartController::class, 'storeRequest'])
        ->name('pieces.request.store');

    Route::get('/mes-demandes', [ClientRequestController::class, 'index'])
        ->name('client.requests.index');

    Route::get('/mes-demandes/{partHoldRequest}', [ClientRequestController::class, 'show'])
        ->name('client.requests.show');
});

Route::get('/pieces/{part}', [ClientPartController::class, 'show'])
    ->name('pieces.show');

Route::middleware(['auth', 'scrapyard'])->group(function (): void {
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

    Route::delete('/casse/vehicules/{vehicle}/photos/{image}', [ScrapyardVehicleController::class, 'destroyImage'])
        ->name('scrapyard.vehicles.images.destroy');

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

    Route::delete('/casse/pieces/{part}/photos/{image}', [ScrapyardPartController::class, 'destroyImage'])
        ->name('scrapyard.parts.images.destroy');

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
});
