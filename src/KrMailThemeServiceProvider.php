<?php

namespace Replworks\KrMailTheme;

use Illuminate\Support\ServiceProvider;

class KrMailThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kr-mail-theme.php', 'kr-mail-theme');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/theme', 'kr-mail-theme');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kr-mail-theme');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/unsubscribe.php');
    }
}
