<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;




Route::get('/', function () {
    return redirect()->route('loginPage');
});
Route::get('/login',[HomeController::class,'gotoLoginPage'])->name('loginPage');
Route::post('/login',[HomeController::class,'loginWeb'])->name('login');
Route::post('/logout',[HomeController::class,'logoutWeb'])->name('logout');
Route::middleware('auth:sanctum')->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get("/dashboard","dashboard")->name("dashboard");
        Route::get('/profile','profile')->name('profile');
    });
});
