<?php

namespace App\Models;

use App\Models\Base\Session as BaseSession;

class Session extends BaseSession
{
	protected $fillable = [
		'user_id',
		'ip_address',
		'user_agent',
		'payload',
		'last_activity'
	];
}
