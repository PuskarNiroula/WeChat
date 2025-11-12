<?php


use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\ExtraController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;




Route::get('/', function () {
    return redirect()->route('loginPage');
});
Route::get('/login',[HomeController::class,'gotoLoginPage'])->name('loginPage');
Route::post('/login',[HomeController::class,'loginWeb'])->name('login');
Route::middleware('auth:sanctum')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout')->name('logout');
    });
    Route::controller(HomeController::class)->group(function () {
        Route::get("/dashboard","dashboard")->name("dashboard");
    });
});

Route::get("/test",[ExtraController::class,"getSidebar"])->name("getSidebarMembers");
Route::get("/test2",function(){
    return view("/main/profile");
});
