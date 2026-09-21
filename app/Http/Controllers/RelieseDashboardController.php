<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class RelieseDashboardController extends Controller
{
    /**
     * Reliese dashboard.
     */
    public function dashboard()
    {
        $tables = $this->getDatabaseTables();
        $modelData = $this->getModelData($tables);

        $totalTables = count($tables);

        $totalModels = collect($modelData)
            ->where('exists', true)
            ->count();

        $totalRelationships = collect($modelData)
            ->sum(fn ($model) => count($model['relationships']));

        $syncedModels = collect($modelData)
            ->where('exists', true)
            ->where('schema_match', true)
            ->count();

        return view('reliese.dashboard', compact(
            'tables',
            'modelData',
            'totalTables',
            'totalModels',
            'totalRelationships',
            'syncedModels'
        ));
    }

    /**
     * Model explorer.
     */
    public function models(Request $request)
    {
        $tables = $this->getDatabaseTables();

        $search = trim($request->get('search', ''));

        $modelData = $this->getModelData($tables);

        if ($search !== '') {
            $modelData = collect($modelData)
                ->filter(function ($model) use ($search) {
                    return Str::contains(
                        strtolower($model['table']),
                        strtolower($search)
                    ) || Str::contains(
                        strtolower($model['model']),
                        strtolower($search)
                    );
                })
                ->values()
                ->all();
        }

        return view('reliese.models', compact(
            'modelData',
            'search'
        ));
    }

    /**
     * Reliese model generation manager.
     */
public function generate(Request $request)
{
    $tables = $this->getDatabaseTables();

    $selectedTable = $request->input('table');

    $message = null;
    $error = null;
    $output = null;

    if ($request->isMethod('post')) {
        try {
            /*
             * Get the PHP executable used by the current Laravel process.
             *
             * On Windows/XAMPP this allows us to execute:
             * php artisan code:models
             */
            $phpBinary = PHP_BINARY;

            /*
             * Laravel project root.
             */
            $artisanPath = base_path('artisan');

            /*
             * Generate model for one selected table.
             */
            if ($selectedTable) {

                /*
                 * Security check:
                 * Only allow tables discovered from the current database.
                 */
                if (!in_array($selectedTable, $tables, true)) {
                    throw new \Exception(
                        'Invalid database table selected.'
                    );
                }

                /*
                 * Build the command.
                 *
                 * Example:
                 * php artisan code:models --table=products
                 */
                $command = sprintf(
                    '"%s" "%s" code:models --table=%s',
                    $phpBinary,
                    $artisanPath,
                    escapeshellarg($selectedTable)
                );

                /*
                 * Execute the command from the Laravel project directory.
                 */
                $process = proc_open(
                    $command,
                    [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ],
                    $pipes,
                    base_path()
                );

                if (!is_resource($process)) {
                    throw new \Exception(
                        'Unable to start the Reliese model generation process.'
                    );
                }

                fclose($pipes[0]);

                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                $exitCode = proc_close($process);

                $output = trim($stdout);

                if ($stderr !== '') {
                    $output .= PHP_EOL . trim($stderr);
                }

                if ($exitCode === 0) {
                    $message =
                        "Reliese model generated successfully for table: {$selectedTable}";
                } else {
                    $error =
                        "Reliese model generation failed for table: {$selectedTable}";
                }
            }

            /*
             * Generate models for all configured tables.
             */
            else {

                /*
                 * Build:
                 *
                 * php artisan code:models
                 */
                $command = sprintf(
                    '"%s" "%s" code:models',
                    $phpBinary,
                    $artisanPath
                );

                /*
                 * Execute command.
                 */
                $process = proc_open(
                    $command,
                    [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ],
                    $pipes,
                    base_path()
                );

                if (!is_resource($process)) {
                    throw new \Exception(
                        'Unable to start the Reliese model generation process.'
                    );
                }

                fclose($pipes[0]);

                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                $exitCode = proc_close($process);

                $output = trim($stdout);

                if ($stderr !== '') {
                    $output .= PHP_EOL . trim($stderr);
                }

                if ($exitCode === 0) {
                    $message =
                        'Reliese models generated successfully for all configured tables.';
                } else {
                    $error =
                        'Reliese model generation failed.';
                }
            }

        } catch (Throwable $e) {

            $error = $e->getMessage();

            if ($output === null) {
                $output = '';
            }

            $output .= PHP_EOL . $e->getMessage();
        }
    }

    return view('reliese.generate', compact(
        'tables',
        'selectedTable',
        'message',
        'error',
        'output'
    ));
}
    /**
     * Schema comparison.
     */
    public function compare(Request $request)
    {
        $tables = $this->getDatabaseTables();

        $selectedTable = $request->get('table');

        $comparison = null;

        if (
            $selectedTable &&
            in_array($selectedTable, $tables, true)
        ) {
            $comparison = $this->compareTableWithModel(
                $selectedTable
            );
        }

        return view('reliese.compare', compact(
            'tables',
            'selectedTable',
            'comparison'
        ));
    }

    /**
     * Get database tables.
     *
     * Tables configured in config/models.php
     * under the "except" option are excluded.
     */
    private function getDatabaseTables(): array
    {
        $databaseName = DB::getDatabaseName();

        $rows = DB::select('SHOW TABLES');

        $key = 'Tables_in_' . $databaseName;

        $tables = collect($rows)
            ->map(function ($row) use ($key) {
                return $row->{$key} ?? null;
            })
            ->filter()
            ->values()
            ->all();

        /*
         * Respect Reliese's configured excluded tables.
         */
        $excludedTables = config('models.except', []);

        if (!is_array($excludedTables)) {
            $excludedTables = [];
        }

        return collect($tables)
            ->reject(function ($table) use ($excludedTables) {
                return in_array(
                    $table,
                    $excludedTables,
                    true
                );
            })
            ->values()
            ->all();
    }

    /**
     * Build model information.
     */
    private function getModelData(array $tables): array
    {
        return collect($tables)
            ->map(function ($table) {
                $model = $this->tableToModel($table);

                $basePath = app_path(
                    'Models/Base/' . $model . '.php'
                );

                $mainPath = app_path(
                    'Models/' . $model . '.php'
                );

                /*
                 * Actual database columns.
                 */
                $columns = Schema::getColumnListing($table);

                /*
                 * Database column details.
                 */
                $columnDetails = collect($columns)
                    ->map(function ($column) use ($table) {
                        return [
                            'name' => $column,
                            'type' => Schema::getColumnType(
                                $table,
                                $column
                            ),
                        ];
                    })
                    ->values()
                    ->all();

                /*
                 * Read generated Base model.
                 */
                $baseContent = File::exists($basePath)
                    ? File::get($basePath)
                    : '';

                /*
                 * Read custom main model.
                 */
                $mainContent = File::exists($mainPath)
                    ? File::get($mainPath)
                    : '';

                /*
                 * Extract Reliese information.
                 */
                $relationships =
                    $this->extractRelationships($baseContent);

                $casts =
                    $this->extractCasts($baseContent);

                $fillable =
                    $this->extractFillable($mainContent);

                $modelColumns =
                    $this->extractModelProperties($baseContent);

                /*
                 * Keep only properties that actually
                 * exist as database columns.
                 *
                 * This prevents relationship properties
                 * from being treated as database columns.
                 */
                $modelColumns = collect($modelColumns)
                    ->filter(function ($column) use ($columns) {
                        return in_array(
                            $column,
                            $columns,
                            true
                        );
                    })
                    ->values()
                    ->all();

                /*
                 * Compare database schema with model.
                 */
                $schemaMatch =
                    $this->checkSchemaMatch(
                        $columns,
                        $modelColumns
                    );

                return [
                    'table' => $table,

                    'model' => $model,

                    'exists' =>
                        File::exists($basePath),

                    'main_exists' =>
                        File::exists($mainPath),

                    'columns' =>
                        $columnDetails,

                    'column_count' =>
                        count($columns),

                    'relationships' =>
                        $relationships,

                    'relationship_count' =>
                        count($relationships),

                    'casts' =>
                        $casts,

                    'fillable' =>
                        $fillable,

                    'model_columns' =>
                        $modelColumns,

                    'schema_match' =>
                        $schemaMatch,

                    'base_path' =>
                        $basePath,

                    'main_path' =>
                        $mainPath,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Convert database table name to model name.
     *
     * Example:
     *
     * products -> Product
     * categories -> Category
     */
    private function tableToModel(string $table): string
    {
        return Str::studly(
            Str::singular($table)
        );
    }

    /**
     * Extract Eloquent relationships from
     * generated Reliese Base model.
     */
    private function extractRelationships(
        string $content
    ): array {
        if ($content === '') {
            return [];
        }

        preg_match_all(
            '/public function\s+([a-zA-Z0-9_]+)\s*\(\s*\).*?\{(.*?)\n\s*\}/s',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $relationships = [];

        foreach ($matches as $match) {
            $methodName = $match[1];
            $body = $match[2];

            /*
             * Detect Eloquent relationship methods.
             */
            if (
                preg_match(
                    '/\b(hasMany|belongsTo|hasOne|belongsToMany|morphMany|morphOne|morphTo|morphToMany|morphedByMany|hasManyThrough|hasOneThrough)\s*\(/',
                    $body
                )
            ) {
                $relationships[] = $methodName;
            }
        }

        return array_values(
            array_unique($relationships)
        );
    }

    /**
     * Extract casts from generated Base model.
     */
    private function extractCasts(
        string $content
    ): array {
        if ($content === '') {
            return [];
        }

        $casts = [];

        if (
            preg_match(
                '/protected\s+\$casts\s*=\s*\[(.*?)\];/s',
                $content,
                $matches
            )
        ) {
            preg_match_all(
                "/['\"]([^'\"]+)['\"]\s*=>\s*['\"]([^'\"]+)['\"]/",
                $matches[1],
                $castMatches,
                PREG_SET_ORDER
            );

            foreach ($castMatches as $cast) {
                $casts[$cast[1]] = $cast[2];
            }
        }

        return $casts;
    }

    /**
     * Extract fillable fields from main model.
     */
    private function extractFillable(
        string $content
    ): array {
        if ($content === '') {
            return [];
        }

        $fillable = [];

        if (
            preg_match(
                '/protected\s+\$fillable\s*=\s*\[(.*?)\];/s',
                $content,
                $matches
            )
        ) {
            preg_match_all(
                "/['\"]([^'\"]+)['\"]/",
                $matches[1],
                $fields
            );

            $fillable = $fields[1] ?? [];
        }

        return $fillable;
    }

    /**
     * Extract properties documented by Reliese.
     */
    private function extractModelProperties(
        string $content
    ): array {
        if ($content === '') {
            return [];
        }

        preg_match_all(
            '/@property(?:-[a-zA-Z0-9_]+)?\s+[^\s]+\s+\$([a-zA-Z0-9_]+)/',
            $content,
            $matches
        );

        return $matches[1] ?? [];
    }

    /**
     * Compare database columns with generated
     * model properties.
     */
    private function checkSchemaMatch(
        array $databaseColumns,
        array $modelColumns
    ): bool {
        if (empty($databaseColumns)) {
            return true;
        }

        if (empty($modelColumns)) {
            return false;
        }

        $databaseColumns = collect($databaseColumns)
            ->map(fn ($column) => strtolower($column))
            ->sort()
            ->values()
            ->all();

        $modelColumns = collect($modelColumns)
            ->map(fn ($column) => strtolower($column))
            ->sort()
            ->values()
            ->all();

        return $databaseColumns === $modelColumns;
    }

    /**
     * Compare a specific database table
     * against its generated Reliese model.
     */
    private function compareTableWithModel(
        string $table
    ): array {
        $model = $this->tableToModel($table);

        $basePath = app_path(
            'Models/Base/' . $model . '.php'
        );

        $exists = File::exists($basePath);

        /*
         * Database columns.
         */
        $databaseColumns =
            Schema::getColumnListing($table);

        $modelColumns = [];
        $casts = [];
        $relationships = [];

        /*
         * Read generated model if it exists.
         */
        if ($exists) {
            $content = File::get($basePath);

            $modelColumns =
                $this->extractModelProperties(
                    $content
                );

            /*
             * Keep only actual database columns.
             */
            $modelColumns = collect($modelColumns)
                ->filter(function ($column) use ($databaseColumns) {
                    return in_array(
                        $column,
                        $databaseColumns,
                        true
                    );
                })
                ->values()
                ->all();

            $casts =
                $this->extractCasts($content);

            $relationships =
                $this->extractRelationships($content);
        }

        /*
         * Normalize database columns.
         */
        $databaseLower = collect($databaseColumns)
            ->map(fn ($column) => strtolower($column));

        /*
         * Normalize model properties.
         */
        $modelLower = collect($modelColumns)
            ->map(fn ($column) => strtolower($column));

        /*
         * Database columns missing from model.
         */
        $missingFromModel =
            $databaseLower
                ->diff($modelLower)
                ->values()
                ->all();

        /*
         * Model properties missing from database.
         */
        $missingFromDatabase =
            $modelLower
                ->diff($databaseLower)
                ->values()
                ->all();

        return [
            'table' =>
                $table,

            'model' =>
                $model,

            'model_exists' =>
                $exists,

            'database_columns' =>
                $databaseColumns,

            'model_columns' =>
                $modelColumns,

            'missing_from_model' =>
                $missingFromModel,

            'missing_from_database' =>
                $missingFromDatabase,

            'casts' =>
                $casts,

            'relationships' =>
                $relationships,

            'matched' =>
                empty($missingFromModel)
                && empty($missingFromDatabase),
        ];
    }
}