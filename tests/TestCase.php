<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Whilesmart\OwnerAccess\OwnerAccessServiceProvider;
use Whilesmart\Transactions\TransactionsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            OwnerAccessServiceProvider::class,
            TransactionsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('transactions.route_middleware', ['api']);
    }
}
