<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RelieseStudioService
{
    /**
     * Get Database Tables and Foreign Key relationships for ER Diagram.
     */
    public function getErDiagramData(): array
    {
        $tableNodes = [];
        $relationships = [];

        // Fetch tables of the current active project database
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
        $projectTables = array_unique($projectTables);

        // System tables to exclude from ER Diagram
        $excluded = [
            'migrations',
            'failed_jobs',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'password_reset_tokens',
            'sessions',
        ];

        $tables = collect($projectTables)
            ->reject(fn($t) => in_array($t, $excluded, true))
            ->values()
            ->all();

        $driver = DB::connection()->getDriverName();
        $foreignKeys = [];

        if ($driver === 'mysql') {
            try {
                $dbName = DB::getDatabaseName();
                $foreignKeys = DB::select("
                    SELECT
                        TABLE_NAME,
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = ?
                      AND REFERENCED_TABLE_NAME IS NOT NULL
                ", [$dbName]);
            } catch (\Throwable $e) {
                $foreignKeys = [];
            }
        }

        // Map Table Columns
        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            $colsData = [];

            foreach ($columns as $col) {
                $colsData[] = [
                    'name' => $col,
                    'type' => Schema::getColumnType($table, $col),
                    'is_primary' => in_array($col, ['id', $table . '_id', 'uuid']),
                ];
            }

            $tableNodes[] = [
                'name' => $table,
                'model' => Str::studly(Str::singular($table)),
                'columns' => $colsData,
            ];
        }

        // 1. Map explicit MySQL Foreign Keys
        $addedPairs = [];
        foreach ($foreignKeys as $fk) {
            if (in_array($fk->TABLE_NAME, $tables) && in_array($fk->REFERENCED_TABLE_NAME, $tables)) {
                $pairKey = $fk->REFERENCED_TABLE_NAME . '-' . $fk->TABLE_NAME . '-' . $fk->COLUMN_NAME;
                $addedPairs[$pairKey] = true;

                $relationships[] = [
                    'from_table' => $fk->REFERENCED_TABLE_NAME,
                    'from_model' => Str::studly(Str::singular($fk->REFERENCED_TABLE_NAME)),
                    'to_table' => $fk->TABLE_NAME,
                    'to_model' => Str::studly(Str::singular($fk->TABLE_NAME)),
                    'foreign_key' => $fk->COLUMN_NAME,
                    'cardinality' => '1:N',
                    'relation_type' => 'hasMany',
                ];
            }
        }

        // 2. Infer naming convention *_id relationships
        foreach ($tableNodes as $node) {
            $tableName = $node['name'];
            foreach ($node['columns'] as $colData) {
                $col = $colData['name'];
                if ($col !== 'id' && str_ends_with($col, '_id')) {
                    $possibleParentSingular = substr($col, 0, -3);
                    $possibleParentPlural = Str::plural($possibleParentSingular);

                    if (in_array($possibleParentPlural, $tables, true) && $possibleParentPlural !== $tableName) {
                        $pairKey = $possibleParentPlural . '-' . $tableName . '-' . $col;
                        if (!isset($addedPairs[$pairKey])) {
                            $addedPairs[$pairKey] = true;
                            $relationships[] = [
                                'from_table' => $possibleParentPlural,
                                'from_model' => Str::studly($possibleParentSingular),
                                'to_table' => $tableName,
                                'to_model' => $node['model'],
                                'foreign_key' => $col,
                                'cardinality' => '1:N',
                                'relation_type' => 'hasMany',
                            ];
                        }
                    }
                }
            }
        }

        return [
            'tables' => $tableNodes,
            'relationships' => $relationships,
            'mermaid' => $this->buildMermaidDefinition($tableNodes, $relationships),
        ];
    }

    /**
     * Build Mermaid.js ER Diagram syntax string.
     */
    private function buildMermaidDefinition(array $tables, array $relationships): string
    {
        $mermaid = "erDiagram\n";

        foreach ($tables as $t) {
            $tableName = $t['name'];
            $mermaid .= "    {$tableName} {\n";
            foreach ($t['columns'] as $col) {
                $type = $col['type'] ?: 'string';
                $name = $col['name'];
                $pk = $col['is_primary'] ? 'PK' : '';
                $mermaid .= "        {$type} {$name} {$pk}\n";
            }
            $mermaid .= "    }\n";
        }

        foreach ($relationships as $rel) {
            $from = $rel['from_table'];
            $to = $rel['to_table'];
            $mermaid .= "    {$from} ||--o{ {$to} : \"has many ({$rel['foreign_key']})\"\n";
        }

        return $mermaid;
    }

    /**
     * Get side-by-side Model Code Diff & Inspection data.
     */
    public function getModelDiff(string $table): array
    {
        $modelName = Str::studly(Str::singular($table));
        $basePath = app_path("Models/Base/{$modelName}.php");
        $mainPath = app_path("Models/{$modelName}.php");

        $baseCode = File::exists($basePath) ? File::get($basePath) : '// Base model file does not exist yet.';
        $mainCode = File::exists($mainPath) ? File::get($mainPath) : '// Main model file does not exist yet.';

        $dbColumns = Schema::getColumnListing($table);
        $columnTypes = [];
        foreach ($dbColumns as $col) {
            $columnTypes[$col] = Schema::getColumnType($table, $col);
        }

        return [
            'table' => $table,
            'model' => $modelName,
            'base_path' => $basePath,
            'main_path' => $mainPath,
            'base_code' => $baseCode,
            'main_code' => $mainCode,
            'db_columns' => $columnTypes,
        ];
    }

    /**
     * Execute live read-only Eloquent query in Sandbox.
     */
    public function executeSandboxQuery(string $modelName, string $withRelations = '', int $limit = 5): array
    {
        $modelClass = "App\\Models\\" . Str::studly($modelName);

        if (!class_exists($modelClass)) {
            // Try base or fallback model
            $modelClass = "App\\Models\\Base\\" . Str::studly($modelName);
            if (!class_exists($modelClass)) {
                return [
                    'success' => false,
                    'error' => "Model class {$modelClass} not found. Please generate models first.",
                ];
            }
        }

        try {
            $t1 = microtime(true);
            $query = $modelClass::query();

            if (!empty($withRelations)) {
                $relations = array_filter(array_map('trim', explode(',', $withRelations)));
                $query->with($relations);
            }

            $results = $query->take(max(1, min(50, $limit)))->get();
            $executionTimeMs = round((microtime(true) - $t1) * 1000, 2);

            return [
                'success' => true,
                'model_class' => $modelClass,
                'count' => $results->count(),
                'execution_time_ms' => $executionTimeMs,
                'data' => $results->toArray(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Sandbox Query Execution Failed: ' . $e->getMessage(),
            ];
        }
    }
}
