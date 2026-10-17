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
        //
    }
}
