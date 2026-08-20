<?php

use App\Http\Controllers\NowPaymentsWebhookController;
use App\Http\Controllers\PaymentPointWebhookController;
use App\Http\Controllers\PocketFiWebhookController;
use App\Http\Controllers\StrollwalletWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/nowpayments/ipn', [NowPaymentsWebhookController::class, 'handle']);
Route::post('/paymentpoint/webhook', [PaymentPointWebhookController::class, 'handle']);
Route::post('/strollwallet/webhook', [StrollwalletWebhookController::class, 'handle']);
Route::post('/pocketfi/webhook', [PocketFiWebhookController::class, 'handle']);
