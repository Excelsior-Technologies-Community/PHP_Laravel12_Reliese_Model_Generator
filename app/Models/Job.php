<?php

namespace App\Models;

use App\Models\Base\Job as BaseJob;

class Job extends BaseJob
{
	protected $fillable = [
		'queue',
		'payload',
		'attempts',
		'reserved_at',
		'available_at'
	];
}
