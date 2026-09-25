<?php

return [
    'namespace' => 'App\Models',
    'parent' => 'Illuminate\Database\Eloquent\Model',
    'uses' => [
        'soft_deletes' => true,
    ],
    'except' => array (
  0 => 'migrations',
  1 => 'failed_jobs',
),
];
