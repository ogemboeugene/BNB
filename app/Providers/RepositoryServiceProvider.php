<?php

namespace App\Providers;

use App\Repositories\BNBRepository;
use App\Repositories\Contracts\BNBRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 * 
 * Service provider for registering repository implementations.
 * This follows the Dependency Inversion Principle by binding
 * interfaces to their concrete implementations.
 * 
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        BNBRepositoryInterface::class => BNBRepository::class,
    ];

    /**
     * Register services.
     * 
     * This method is called by Laravel to register services in the container.
     * We bind repository interfaces to their concrete implementations here.
     */
    public function register(): void
    {
        // The bindings are automatically registered by Laravel
        // due to the $bindings property above.
        
        // Alternatively, you can manually bind services:
        // $this->app->bind(BNBRepositoryInterface::class, BNBRepository::class);
    }

    /**
     * Bootstrap services.
     * 
     * This method is called after all other service providers have been registered.
     * You can use this method to perform any necessary bootstrapping operations.
     */
    public function boot(): void
    {
        // No specific bootstrap operations needed for repositories
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            BNBRepositoryInterface::class,
        ];
    }
}
