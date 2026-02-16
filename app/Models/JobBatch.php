<?php

namespace App\Models;

use App\Models\Base\JobBatch as BaseJobBatch;

class JobBatch extends BaseJobBatch
{
	protected $fillable = [
		'name',
		'total_jobs',
		'pending_jobs',
		'failed_jobs',
		'failed_job_ids',
		'options',
		'cancelled_at',
		'finished_at'
	];
}
