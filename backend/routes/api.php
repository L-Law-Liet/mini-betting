<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,BetController,EventController};


Route::post('/login',[AuthController::class,'login']);


Route::middleware(['hmac.signature'])->group(function() {
    Route::get('/events',[EventController::class,'index']);

    Route::middleware(['auth:sanctum','throttle:bets'])->group(function() {
        Route::get('/bets',[BetController::class,'index']);
        Route::post('/bets',[BetController::class,'store'])->middleware(['idempotency']);
    });
});
