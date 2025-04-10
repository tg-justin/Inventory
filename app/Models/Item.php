<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
	use HasFactory;

	// Add fillable properties later as needed for mass assignment
	protected $fillable = [
		'name',
		'description',
		'sku',
		'category_id',
		'unit',
		'cost_per_unit',
		'value_per_unit',
		'reorder_threshold',
		'image_path',
		'is_active',
		'notes',
	];

	public function category(): BelongsTo
	{
		return $this->belongsTo(Category::class);
	}

	public function inventoryStock(): HasMany
	{
		return $this->hasMany(InventoryStock::class);
	}

	public function conventionRequestItems(): HasMany
	{
		return $this->hasMany(ConventionRequestItem::class);
	}

	// Add other relationships as needed...
}