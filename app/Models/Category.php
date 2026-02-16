<?php

namespace App\Models;

use App\Models\Base\Category as BaseCategory;

class Category extends BaseCategory
{
	protected $fillable = [
		'name'
	];
}
