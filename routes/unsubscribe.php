<?php

use Illuminate\Support\Facades\Route;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeController;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;

Route::match(['get', 'post'], config('kr-mail-theme.unsubscribe_path', 'unsubscribe'), UnsubscribeController::class)
    ->name(UnsubscribeUrlGenerator::ROUTE_NAME);
