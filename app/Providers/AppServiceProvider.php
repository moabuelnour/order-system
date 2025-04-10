<?php

namespace App\Providers;

use App\Infrastructure\Mail\StockAlert;
use App\Infrastructure\Repositories\EloquentIngredientRepository;
use App\Infrastructure\Repositories\EloquentOrderRepository;
use App\Interfaces\IngredientRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\StockNotifierInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(IngredientRepositoryInterface::class, EloquentIngredientRepository::class);
        // $this->app->bind(StockNotifierInterface::class, StockAlert::class);
        $this->app->bind(StockNotifierInterface::class, function () {
            // Return a closure that requires manual instantiation later
            return new StockAlert(new \App\Domain\Entities\Ingredient('', 0, 0)); // Dummy instance, replaced in use case
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
