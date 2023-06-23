<?php

declare(strict_types=1);

namespace AMgrade\SingleRole;

use AMgrade\SingleRole\Services\SingleRoleService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class SingleRoleServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/single-role.php' => $this->app->configPath('single-role.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../migrations' => $this->app->databasePath('migrations'),
            ], 'migrations');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->resourcePath('lang'),
            ], 'translations');
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'single-role');

        $this->registerService();
        $this->registerBladeDirectives();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/single-role.php', 'single-role');
    }

    protected function registerService(): void
    {
        $this->app->singleton(SingleRoleService::class);
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('role', function ($role, ...$guards) {
            return $this->app
                ->make(SingleRoleService::class)
                ->hasRole($role, $this->app->make('request'), $guards);
        });

        Blade::if('permission', function ($permission, ...$guards) {
            return $this->app
                ->make(SingleRoleService::class)
                ->hasPermission($permission, $this->app->make('request'), $guards);
        });
    }
}
