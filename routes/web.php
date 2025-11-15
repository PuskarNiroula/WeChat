<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('loginPage');
});
Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->middleware(['auth']);
Route::controller(HomeController::class)->group(function () {
    Route::get('/login','gotoLoginPage')->name('loginPage');
    Route::get('/forgetPassword',"forgotPassword")->name("forgotPassword");
    Route::get('/resetPassword/{token}','resetPassword')->name('resetPassword');
    Route::post('/login','loginWeb')->name('login');
    Route::post('/logout','logoutWeb')->name('logout');
    Route::get('/register','gotoRegisterPage')->name('registerPage');
    Route::post('/register','registerWeb')->name('register');
});
Route::middleware('auth:sanctum')->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get("/dashboard","dashboard")->name("dashboard");
        Route::get('/profile','profile')->name('profile');
    });
});
