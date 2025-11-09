<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExtraController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API works']);
});
Route::controller(AuthController::class)->group(function () {
    Route::post('/api/login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(MessageController::class)->group(function () {
        Route::get('/getMessages/{id}', 'getChunkMessages')->name('getMessages');
    });
    Route::controller(ExtraController::class)->group(function () {
        Route::get('/search/{string}', 'search')->name('getUsers');
    });
});


