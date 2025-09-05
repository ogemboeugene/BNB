<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

/**
 * Class PaginationServiceProvider
 * 
 * Service provider for configuring pagination settings across the application.
 * Sets up default pagination parameters and customizes pagination views.
 * 
 * @package App\Providers
 */
class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * 
     * @return void
     */
    public function register(): void
    {
        // Bind pagination configuration
        $this->app->bind('pagination.config', function () {
            return [
                'default_per_page' => 15,
                'max_per_page' => 100,
                'min_per_page' => 1,
                'page_name' => 'page',
                'per_page_name' => 'per_page',
            ];
        });
    }

    /**
     * Bootstrap services.
     * 
     * @return void
     */
    public function boot(): void
    {
        // Set default pagination view for API responses
        // Note: Views are not typically used for API-only applications
        
        // Set default pagination parameters
        $this->setDefaultPaginationParameters();
    }

    /**
     * Set default pagination parameters.
     * 
     * @return void
     */
    private function setDefaultPaginationParameters(): void
    {
        // Set the default number of items per page
        config(['app.pagination.default_per_page' => 15]);
        config(['app.pagination.max_per_page' => 100]);
        
        // Resolve page name from request
        Paginator::currentPageResolver(function ($pageName = 'page') {
            return request()->input($pageName, 1);
        });
        
        // Resolve path for pagination links
        Paginator::currentPathResolver(function () {
            return request()->url();
        });
    }
}
