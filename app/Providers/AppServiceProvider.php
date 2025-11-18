<?php

namespace App\Providers;

use App\Interface\ConversationRepositoryInterface;
use App\Interface\ConversationUserRepositoryInterface;
use App\Interface\LastMessageRepositoryInterface;
use App\Interface\MessageRepositoryInterface;
use App\Interface\UserRepoInterface;
use App\Repository\ConversationRepository;
use App\Repository\ConversationUserRepository;
use App\Repository\LastMessageRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepoInterface::class, UserRepository::class);
        $this->app->bind(ConversationRepositoryInterface::class, ConversationRepository::class);
        $this->app->bind(ConversationUserRepositoryInterface::class, ConversationUserRepository::class);
        $this->app->bind(LastMessageRepositoryInterface::class, LastMessageRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);


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
