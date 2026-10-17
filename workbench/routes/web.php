<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\MetaController;
use Workbench\App\Http\Controllers\PreviewController;
use Workbench\App\Http\Controllers\SendController;

Route::get('/', fn () => view('workbench::preview', [
    'mailpitUrl' => env('MAILPIT_URL', 'http://127.0.0.1:38025'),
    'script' => file_get_contents(__DIR__.'/../resources/js/scr-001.js'),
]))->name('workbench.scr-001');

Route::get('/preview', PreviewController::class)->name('workbench.preview');

Route::get('/meta', MetaController::class)->name('workbench.meta');

Route::post('/send', SendController::class)->name('workbench.send');
