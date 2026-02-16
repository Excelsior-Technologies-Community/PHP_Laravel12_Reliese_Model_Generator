<?php

namespace App\Models;

use App\Models\Base\Product as BaseProduct;

class Product extends BaseProduct
{
	protected $fillable = [
		'category_id',
		'name',
		'price'
	];
}
