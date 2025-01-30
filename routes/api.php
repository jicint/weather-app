<?php

use App\Http\Controllers\API\TravelController;
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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/travel/recommendations', [TravelController::class, 'getRecommendations']);
    Route::get('/travel/weather/{location}/{date}', [TravelController::class, 'getDetailedWeather']);
    Route::get('/travel/history', [TravelController::class, 'getTravelHistory']);
    Route::post('/travel/{id}/favorite', [TravelController::class, 'toggleFavorite']);
    Route::get('/travel/tips/{destination}', [TravelController::class, 'getTravelTips']);
    Route::post('/travel/bookings', [TravelController::class, 'createBooking']);
    Route::get('/travel/bookings', [TravelController::class, 'getUserBookings']);
});
