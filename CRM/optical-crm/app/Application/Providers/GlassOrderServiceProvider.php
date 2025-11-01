<?php

namespace App\Application\Providers;

use App\Application\Services\GlassOrderService;
use App\Domain\Glasses\Repositories\GlassOrderRepositoryInterface;
use App\Infrastructure\Repositories\EloquentGlassOrderRepository;
use Illuminate\Support\ServiceProvider;

class GlassOrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the repository interface to its implementation
        $this->app->bind(
            GlassOrderRepositoryInterface::class,
            EloquentGlassOrderRepository::class
        );

        // Register the service
        $this->app->singleton(GlassOrderService::class, function ($app) {
            return new GlassOrderService(
                $app->make(GlassOrderRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
