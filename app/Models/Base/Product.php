<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property float $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Category $category
 *
 * @package App\Models\Base
 */
class Product extends Model
{
	protected $table = 'products';

	protected $casts = [
		'category_id' => 'int',
		'price' => 'float'
	];

	public function category()
	{
		return $this->belongsTo(Category::class);
	}
}
