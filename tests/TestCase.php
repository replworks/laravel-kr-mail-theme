<?php

namespace Replworks\KrMailTheme\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Replworks\KrMailTheme\KrMailThemeServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [KrMailThemeServiceProvider::class];
    }
}
