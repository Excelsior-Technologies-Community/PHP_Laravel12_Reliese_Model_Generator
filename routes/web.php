<?php

use App\Http\Controllers\RelieseDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('reliese.dashboard');
});

Route::prefix('reliese')
    ->name('reliese.')
    ->controller(RelieseDashboardController::class)
    ->group(function () {

        Route::get('/dashboard', 'dashboard')
            ->name('dashboard');

        Route::get('/models', 'models')
            ->name('models');

        Route::match(['get', 'post'], '/generate', 'generate')
            ->name('generate');

        Route::get('/compare', 'compare')
            ->name('compare');
    });