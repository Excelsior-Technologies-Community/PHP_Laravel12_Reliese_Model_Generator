<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

use App\Services\RelieseStudioService;

class RelieseDashboardController extends Controller
{
    public function __construct(
        protected RelieseStudioService $studioService
    ) {
    }
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
            ->sum(fn($model) => count($model['relationships']));

        $syncedModels = collect($modelData)
            ->where('exists', true)
            ->where('schema_match', true)
            ->count();

        $missingModels = collect($modelData)
            ->where('exists', false)
            ->count();

        $outOfSyncModels = collect($modelData)
            ->where('exists', true)
            ->where('schema_match', false)
            ->count();

        return view('reliese.dashboard', compact(
            'tables',
            'modelData',
            'totalTables',
            'totalModels',
            'totalRelationships',
            'syncedModels',
            'missingModels',
            'outOfSyncModels'
        ));
    }

    /**
     * Model explorer.
     *
     * Search
     * Status filter
     * Schema filter
     * Sorting
     * Number-only pagination
     */
    public function models(Request $request)
    {
        $tables = $this->getDatabaseTables();

        $search = trim($request->get('search', ''));

        $status = $request->get('status', 'all');

        $schema = $request->get('schema', 'all');

        $sort = $request->get('sort', 'table');

        $direction = $request->get('direction', 'asc');

        $perPage = (int) $request->get('per_page', 5);

        $allowedPerPage = [5, 10, 15, 20];

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }

        $modelData = collect(
            $this->getModelData($tables)
        );

        /*
         * Search.
         */
        if ($search !== '') {
            $modelData = $modelData->filter(function ($model) use ($search) {
                return Str::contains(
                    strtolower($model['table']),
                    strtolower($search)
                )
                    ||
                    Str::contains(
                        strtolower($model['model']),
                        strtolower($search)
                    );
            });
        }

        /*
         * Model status filter.
         */
        if ($status === 'generated') {
            $modelData = $modelData->where('exists', true);
        }

        if ($status === 'missing') {
            $modelData = $modelData->where('exists', false);
        }

        /*
         * Schema filter.
         */
        if ($schema === 'synced') {
            $modelData = $modelData
                ->where('exists', true)
                ->where('schema_match', true);
        }

        if ($schema === 'check') {
            $modelData = $modelData
                ->where('exists', true)
                ->where('schema_match', false);
        }

        /*
         * Sorting.
         */
        $allowedSorts = [
            'table',
            'model',
            'column_count',
            'relationship_count',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'table';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $modelData = $modelData->sortBy(
            $sort,
            SORT_NATURAL | SORT_FLAG_CASE,
            $direction === 'desc'
        );

        $modelData = $modelData
            ->values();

        /*
         * Pagination.
         */
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $total = $modelData->count();

        $items = $modelData
            ->slice(
                ($currentPage - 1) * $perPage,
                $perPage
            )
            ->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('reliese.models', [
            'modelData' => $paginator,
            'search' => $search,
            'status' => $status,
            'schema' => $schema,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ]);
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

                $phpBinary = PHP_BINARY;

                $artisanPath = base_path('artisan');

                /*
                 * Generate specific table.
                 */
                if ($selectedTable) {

                    if (!in_array(
                        $selectedTable,
                        $tables,
                        true
                    )) {
                        throw new \Exception(
                            'Invalid database table selected.'
                        );
                    }

                    $result = $this->runRelieseCommand(
                        $phpBinary,
                        $artisanPath,
                        [
                            'code:models',
                            '--table=' . $selectedTable,
                        ]
                    );

                    $output = $result['output'];

                    if ($result['exitCode'] === 0) {

                        $message =
                            "Reliese model generated successfully for table: {$selectedTable}";
                    } else {

                        $error =
                            "Reliese model generation failed for table: {$selectedTable}";
                    }
                }

                /*
                 * Generate all models.
                 */ else {

                    $result = $this->runRelieseCommand(
                        $phpBinary,
                        $artisanPath,
                        [
                            'code:models',
                        ]
                    );

                    $output = $result['output'];

                    if ($result['exitCode'] === 0) {

                        $message =
                            'Reliese models generated successfully for all configured tables.';
                    } else {

                        $error =
                            'Reliese model generation failed.';
                    }
                }
            } catch (Throwable $e) {

                $error = $e->getMessage();

                $output = ($output ?? '')
                    . PHP_EOL
                    . $e->getMessage();
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
     * Regenerate all missing/out-of-sync models.
     */
    public function regenerateOutOfSync()
    {
        $tables = $this->getDatabaseTables();

        $modelData = $this->getModelData($tables);

        $targets = collect($modelData)
            ->filter(function ($model) {
                return !$model['exists']
                    || !$model['schema_match'];
            })
            ->pluck('table')
            ->values()
            ->all();

        if (empty($targets)) {

            return redirect()
                ->route('reliese.generate')
                ->with(
                    'success',
                    'All models are already generated and synchronized.'
                );
        }

        $successCount = 0;

        $failedCount = 0;

        $messages = [];

        foreach ($targets as $table) {

            try {

                $result = $this->runRelieseCommand(
                    PHP_BINARY,
                    base_path('artisan'),
                    [
                        'code:models',
                        '--table=' . $table,
                    ]
                );

                if ($result['exitCode'] === 0) {

                    $successCount++;

                    $messages[] =
                        "✓ {$table} generated successfully.";
                } else {

                    $failedCount++;

                    $messages[] =
                        "✗ {$table} generation failed.";
                }
            } catch (Throwable $e) {

                $failedCount++;

                $messages[] =
                    "✗ {$table}: {$e->getMessage()}";
            }
        }

        return redirect()
            ->route('reliese.generate')
            ->with(
                'success',
                "Regeneration completed. {$successCount} successful, {$failedCount} failed."
            )
            ->with(
                'generation_details',
                $messages
            );
    }

    /**
     * Export model report as CSV.
     */
    public function exportCsv()
    {
        $tables = $this->getDatabaseTables();

        $modelData = $this->getModelData($tables);

        $filename =
            'reliese-model-report-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' =>
            'attachment; filename="' . $filename . '"',
        ];

        return Response::stream(function () use ($modelData) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Table',
                'Model',
                'Model Status',
                'Schema Status',
                'Columns',
                'Relationships',
                'Casts',
                'Fillable Fields',
                'Base Model',
                'Main Model',
            ]);

            foreach ($modelData as $model) {

                fputcsv($handle, [
                    $model['table'],
                    $model['model'],
                    $model['exists']
                        ? 'Generated'
                        : 'Missing',
                    $model['schema_match']
                        ? 'Synced'
                        : 'Needs Check',
                    $model['column_count'],
                    $model['relationship_count'],
                    count($model['casts']),
                    count($model['fillable']),
                    $model['base_path'],
                    $model['main_path'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export model report as JSON.
     */
    public function exportJson()
    {
        $tables = $this->getDatabaseTables();

        $modelData = $this->getModelData($tables);

        return response()->json([
            'generated_at' => now()->toDateTimeString(),

            'summary' => [
                'total_tables' => count($tables),

                'total_models' =>
                collect($modelData)
                    ->where('exists', true)
                    ->count(),

                'synced_models' =>
                collect($modelData)
                    ->where('exists', true)
                    ->where('schema_match', true)
                    ->count(),

                'missing_models' =>
                collect($modelData)
                    ->where('exists', false)
                    ->count(),

                'out_of_sync_models' =>
                collect($modelData)
                    ->where('exists', true)
                    ->where('schema_match', false)
                    ->count(),

                'total_relationships' =>
                collect($modelData)
                    ->sum(
                        fn($model) =>
                        count($model['relationships'])
                    ),
            ],

            'models' => $modelData,
        ])
            ->header(
                'Content-Disposition',
                'attachment; filename="reliese-model-report.json"'
            );
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
            in_array(
                $selectedTable,
                $tables,
                true
            )
        ) {

            $comparison =
                $this->compareTableWithModel(
                    $selectedTable
                );
        }

        return view(
            'reliese.compare',
            compact(
                'tables',
                'selectedTable',
                'comparison'
            )
        );
    }

    /**
     * Get database tables.
     */
    private function getDatabaseTables(): array
    {
        $activeDbLower = strtolower(DB::getDatabaseName());
        $allTables = Schema::getTableListing();

        $projectTables = [];
        foreach ($allTables as $t) {
            if (Str::contains($t, '.')) {
                [$db, $tbl] = explode('.', $t, 2);
                if (strtolower($db) === $activeDbLower) {
                    $projectTables[] = $tbl;
                }
            } else {
                $projectTables[] = $t;
            }
        }
        $tables = array_values(array_unique($projectTables));

        $excludedTables =
            config('models.except', []);

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
    private function getModelData(
        array $tables
    ): array {

        return collect($tables)
            ->map(function ($table) {

                $model =
                    $this->tableToModel($table);

                $basePath =
                    app_path(
                        'Models/Base/' .
                            $model .
                            '.php'
                    );

                $mainPath =
                    app_path(
                        'Models/' .
                            $model .
                            '.php'
                    );

                $columns =
                    Schema::getColumnListing(
                        $table
                    );

                $columnDetails =
                    collect($columns)
                    ->map(function ($column) use ($table) {

                        return [
                            'name' => $column,

                            'type' =>
                            Schema::getColumnType(
                                $table,
                                $column
                            ),
                        ];
                    })
                    ->values()
                    ->all();

                $baseContent =
                    File::exists($basePath)
                    ? File::get($basePath)
                    : '';

                $mainContent =
                    File::exists($mainPath)
                    ? File::get($mainPath)
                    : '';

                $relationships =
                    $this->extractRelationships(
                        $baseContent
                    );

                $casts =
                    $this->extractCasts(
                        $baseContent
                    );

                $fillable =
                    $this->extractFillable(
                        $mainContent
                    );

                $modelColumns =
                    $this->extractModelProperties(
                        $baseContent
                    );

                $modelColumns =
                    collect($modelColumns)
                    ->filter(function ($column) use ($columns) {

                        return in_array(
                            $column,
                            $columns,
                            true
                        );
                    })
                    ->values()
                    ->all();

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
     * Execute Reliese Artisan command.
     */
    private function runRelieseCommand(
        string $phpBinary,
        string $artisanPath,
        array $arguments
    ): array {

        $command =
            '"' .
            $phpBinary .
            '" "' .
            $artisanPath .
            '"';

        foreach ($arguments as $argument) {

            $command .=
                ' ' .
                escapeshellarg($argument);
        }

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

        $stdout =
            stream_get_contents($pipes[1]);

        fclose($pipes[1]);

        $stderr =
            stream_get_contents($pipes[2]);

        fclose($pipes[2]);

        $exitCode =
            proc_close($process);

        $output =
            trim($stdout);

        if ($stderr !== '') {

            $output .=
                PHP_EOL .
                trim($stderr);
        }

        return [
            'exitCode' => $exitCode,

            'output' => $output,
        ];
    }

    /**
     * Convert table to model name.
     */
    private function tableToModel(
        string $table
    ): string {

        return Str::studly(
            Str::singular($table)
        );
    }

    /**
     * Extract relationships.
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

            $methodName =
                $match[1];

            $body =
                $match[2];

            if (
                preg_match(
                    '/\b(hasMany|belongsTo|hasOne|belongsToMany|morphMany|morphOne|morphTo|morphToMany|morphedByMany|hasManyThrough|hasOneThrough)\s*\(/',
                    $body
                )
            ) {

                $relationships[] =
                    $methodName;
            }
        }

        return array_values(
            array_unique(
                $relationships
            )
        );
    }

    /**
     * Extract casts.
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

                $casts[$cast[1]] =
                    $cast[2];
            }
        }

        return $casts;
    }

    /**
     * Extract fillable.
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

            $fillable =
                $fields[1] ?? [];
        }

        return $fillable;
    }

    /**
     * Extract model properties.
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
     * Compare schema.
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

        $databaseColumns =
            collect($databaseColumns)
            ->map(
                fn($column) =>
                strtolower($column)
            )
            ->sort()
            ->values()
            ->all();

        $modelColumns =
            collect($modelColumns)
            ->map(
                fn($column) =>
                strtolower($column)
            )
            ->sort()
            ->values()
            ->all();

        return $databaseColumns === $modelColumns;
    }

    /**
     * Compare table with model.
     */
    private function compareTableWithModel(
        string $table
    ): array {

        $model =
            $this->tableToModel($table);

        $basePath =
            app_path(
                'Models/Base/' .
                    $model .
                    '.php'
            );

        $exists =
            File::exists($basePath);

        $databaseColumns =
            Schema::getColumnListing($table);

        $modelColumns = [];

        $casts = [];

        $relationships = [];

        if ($exists) {

            $content =
                File::get($basePath);

            $modelColumns =
                $this->extractModelProperties(
                    $content
                );

            $modelColumns =
                collect($modelColumns)
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
                $this->extractCasts(
                    $content
                );

            $relationships =
                $this->extractRelationships(
                    $content
                );
        }

        $databaseLower =
            collect($databaseColumns)
            ->map(
                fn($column) =>
                strtolower($column)
            );

        $modelLower =
            collect($modelColumns)
            ->map(
                fn($column) =>
                strtolower($column)
            );

        $missingFromModel =
            $databaseLower
            ->diff($modelLower)
            ->values()
            ->all();

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
                &&
                empty($missingFromDatabase),
        ];
    }

    /**
     * Interactive Custom Reliese Configurator Studio View.
     */
    public function configurator(Request $request)
    {
        $tables = $this->getDatabaseTables();
        $modelsConfigPath = config_path('models.php');
        $rawConfig = File::exists($modelsConfigPath) ? File::get($modelsConfigPath) : '';

        $currentConfig = [
            'namespace' => config('models.namespace', 'App\\Models'),
            'parent' => config('models.parent', 'Illuminate\\Database\\Eloquent\\Model'),
            'use_soft_deletes' => config('models.uses.soft_deletes', true),
            'snake_attributes' => config('models.snake_attributes', true),
            'dates_format' => config('models.dates.format', 'Y-m-d H:i:s'),
            'except' => implode(', ', config('models.except', [])),
        ];

        return view('reliese.configurator', compact('tables', 'currentConfig', 'rawConfig'));
    }

    /**
     * Update Reliese Configuration settings.
     */
    public function updateConfig(Request $request)
    {
        $modelsConfigPath = config_path('models.php');

        $namespace = trim($request->input('namespace', 'App\\Models'));
        $parent = trim($request->input('parent', 'Illuminate\\Database\\Eloquent\\Model'));
        $softDeletes = $request->has('use_soft_deletes');
        $exceptInput = trim($request->input('except', ''));
        $exceptArray = array_filter(array_map('trim', explode(',', $exceptInput)));

        // Create updated config stub
        $exceptPhp = var_export($exceptArray, true);
        $softDeletesPhp = $softDeletes ? 'true' : 'false';

        $configStub = "<?php

return [
    'namespace' => '{$namespace}',
    'parent' => '{$parent}',
    'uses' => [
        'soft_deletes' => {$softDeletesPhp},
    ],
    'except' => {$exceptPhp},
];
";

        File::put($modelsConfigPath, $configStub);

        return redirect()->route('reliese.configurator')
            ->with('success', 'Reliese Model Generator configuration updated successfully.');
    }

    /**
     * Database ER Diagram & Relational Dependency Visualizer Studio View.
     */
    public function erDiagram(Request $request)
    {
        $erData = $this->studioService->getErDiagramData();

        return view('reliese.er-diagram', compact('erData'));
    }

    /**
     * ER Diagram JSON Data API Endpoint.
     */
    public function erDiagramData(Request $request)
    {
        $erData = $this->studioService->getErDiagramData();

        return response()->json([
            'status' => 'success',
            'tables_count' => count($erData['tables']),
            'relationships_count' => count($erData['relationships']),
            'data' => $erData,
        ]);
    }

    /**
     * Code Inspection, Live Model Diff Inspector & Eloquent Sandbox View.
     */
    public function diffSandbox(Request $request)
    {
        $tables = $this->getDatabaseTables();
        $selectedTable = $request->get('table', $tables[0] ?? 'users');

        $diffData = $this->studioService->getModelDiff($selectedTable);

        return view('reliese.diff-sandbox', compact('tables', 'selectedTable', 'diffData'));
    }

    /**
     * Execute live read-only Eloquent query in Sandbox API.
     */
    public function executeSandbox(Request $request)
    {
        $modelName = trim($request->input('model', 'User'));
        $withRelations = trim($request->input('with', ''));
        $limit = (int) $request->input('limit', 5);

        $result = $this->studioService->executeSandboxQuery($modelName, $withRelations, $limit);

        return response()->json($result);
    }
}
