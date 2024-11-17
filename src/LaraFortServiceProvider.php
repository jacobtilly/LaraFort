<?php

namespace JacobTilly\LaraFort;

use Illuminate\Support\ServiceProvider;
use JacobTilly\LaraFort\Commands\InstallCommand;
use JacobTilly\LaraFort\Commands\MigrateCommand;
use JacobTilly\LaraFort\Commands\RefreshTokensCommand;
use JacobTilly\LaraFort\Services\LaraFortService;
use Illuminate\Console\Scheduling\Schedule;
use JacobTilly\LaraFort\Commands\StopTunnelCommand;
use JacobTilly\LaraFort\Commands\SwitchEnvironmentCommand;
use JacobTilly\LaraFort\Commands\TestConnectionCommand;
use JacobTilly\LaraFort\Commands\UninstallCommand;

class LaraFortServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/larafort.php', 'larafort'
        );

        $this->app->singleton('larafort', function ($app) {
            return new LaraFortService();
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                MigrateCommand::class,
                RefreshTokensCommand::class,
                TestConnectionCommand::class,
                UninstallCommand::class,
                SwitchEnvironmentCommand::class,
                StopTunnelCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/larafort.php' => config_path('larafort.php'),
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'larafort');

            // Register scheduled task
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('larafort:refresh-tokens')->daily();
            });
        }
    }
}
