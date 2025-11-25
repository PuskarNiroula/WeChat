<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExtraController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;


Route::get('/test', function () {
    return response()->json(['message' => 'API works']);
});

Route::controller(AuthController::class)->group(function () {

    Route::post('/api/login', 'login')->name('api.login');
    Route::post('/sendPasswordResetLinkEmail', 'sendResetLink')->name('password.email');
    Route::post('/api/resetPassword/{token}/{email}', 'resetPassword')->name('password.update');
});
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/api/save-public-keys',  [AuthController::class,'storePublicKey'])->name('api.save_keys');
    Route::post('/api/logout', [AuthController::class, 'logout'])->name('api.logout');;
    Route::controller(MessageController::class)->group(function () {
        Route::get('/getMessages/{id}', 'getChunkMessages')->name('getMessages');
        Route::post('/sendMessage', 'sendMessage')->name('sendMessage');
        Route::get("/openChat/{id}","createOrFindConversation")->name("openChat");
    });
    Route::controller(ExtraController::class)->group(function () {
        Route::get('/search/{string}', 'search')->name('searchUsers');
        Route::get("/getSidebarMembers","getSidebar")->name("getSidebarMembers");
    });
    Route::post('/updateProfile',[ProfileController::class,'updateProfile'])->name('updateProfile');
});


