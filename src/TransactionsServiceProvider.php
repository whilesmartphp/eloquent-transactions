<?php

namespace Whilesmart\Transactions;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TransactionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/transactions.php', 'transactions');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/transactions.php' => config_path('transactions.php'),
        ], 'transactions-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'transactions-migrations');

        if (config('transactions.register_routes', true)) {
            Route::middleware(config('transactions.route_middleware', ['api', 'auth:sanctum']))
                ->prefix(config('transactions.route_prefix', 'api'))
                ->group(__DIR__.'/../routes/api.php');
        }
    }
}
