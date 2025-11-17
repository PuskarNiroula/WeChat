<?php

namespace App\Providers;

use App\Interface\UserRepoInterface;
use App\Repository\UserRepository;
use App\Service\UserService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepoInterface::class,UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::middleware('api')
            ->group(base_path('routes/api.php'));
    }
}
