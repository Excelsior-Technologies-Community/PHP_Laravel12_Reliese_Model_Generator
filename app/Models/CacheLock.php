<?php

namespace App\Models;

use App\Models\Base\CacheLock as BaseCacheLock;

class CacheLock extends BaseCacheLock
{
	protected $fillable = [
		'owner',
		'expiration'
	];
}
