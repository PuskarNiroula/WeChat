<?php


use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/fire-test', function () {
    event(new App\Events\TestEvent("Hello from Laravel!"));
    return "Event fired!";
});


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

Route::get("/test",[\App\Http\Controllers\Api\ExtraController::class,"getSidebar"])->name("getSidebarMembers");
