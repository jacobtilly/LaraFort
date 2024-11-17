<?php

namespace JacobTilly\LaraFort\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use JacobTilly\LaraFort\LaraFortServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaraFortServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Test database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // LaraFort config
        $app['config']->set('larafort.client_id', 'test-client-id');
        $app['config']->set('larafort.client_secret', 'test-client-secret');
        $app['config']->set('larafort.environment', 'test');
        $app['config']->set('larafort.tunnel', 'url');
        $app['config']->set('larafort.callback_url', 'https://test.example.com');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
