<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'parent_id',
		'sort_order',
	];

	/**
	 * Get the parent category.
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(Category::class, 'parent_id');
	}

	/**
	 * Get the child categories.
	 */
	public function children(): HasMany
	{
		return $this->hasMany(Category::class, 'parent_id');
	}

	/**
	 * Get the items belonging to this category.
	 */
	public function items(): HasMany
	{
		return $this->hasMany(Item::class);
	}
}