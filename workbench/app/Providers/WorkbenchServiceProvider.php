<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Replworks\KrMailTheme\KrMailThemeServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(KrMailThemeServiceProvider::class);
    }

    public function boot(): void
    {
        $samples = require __DIR__.'/../../samples.php';

        foreach ($samples['brand'] as $key => $value) {
            config()->set("kr-mail-theme.{$key}", $value);
        }
    }
}
