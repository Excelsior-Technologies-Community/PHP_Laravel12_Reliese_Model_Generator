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

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', 'dashboard')
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Model Explorer
        |--------------------------------------------------------------------------
        */

        Route::get('/models', 'models')
            ->name('models');

        /*
        |--------------------------------------------------------------------------
        | Generation
        |--------------------------------------------------------------------------
        */

        Route::match(
            ['get', 'post'],
            '/generate',
            'generate'
        )->name('generate');

        /*
        |--------------------------------------------------------------------------
        | Regenerate Missing / Out-of-Sync
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/regenerate-out-of-sync',
            'regenerateOutOfSync'
        )->name('regenerate.outofsync');

        /*
        |--------------------------------------------------------------------------
        | Schema Comparison
        |--------------------------------------------------------------------------
        */

        Route::get('/compare', 'compare')
            ->name('compare');

        /*
        |--------------------------------------------------------------------------
        | Exports
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/export/csv',
            'exportCsv'
        )->name('export.csv');

        Route::get(
            '/export/json',
            'exportJson'
        )->name('export.json');

        /*
        |--------------------------------------------------------------------------
        | NEW: Reliese Custom Configurator Studio
        |--------------------------------------------------------------------------
        */

        Route::get('/configurator', 'configurator')
            ->name('configurator');

        Route::post('/configurator', 'updateConfig')
            ->name('configurator.update');

        /*
        |--------------------------------------------------------------------------
        | NEW: Database ER Diagram Visualizer
        |--------------------------------------------------------------------------
        */

        Route::get('/er-diagram', 'erDiagram')
            ->name('er_diagram');

        Route::get('/er-diagram-data', 'erDiagramData')
            ->name('er_diagram.data');

        /*
        |--------------------------------------------------------------------------
        | NEW: Live Model Diff & Eloquent Sandbox Playground
        |--------------------------------------------------------------------------
        */

        Route::get('/diff-sandbox', 'diffSandbox')
            ->name('diff_sandbox');

        Route::post('/sandbox/execute', 'executeSandbox')
            ->name('sandbox.execute');
    });