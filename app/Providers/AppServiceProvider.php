<?php

namespace App\Providers;

use App\Repositories\BNBRepository;
use App\Repositories\Contracts\BNBRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interface to concrete implementation
        $this->app->bind(BNBRepositoryInterface::class, BNBRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
