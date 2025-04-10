<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
	use HasFactory;

	protected $table = 'inventory_stock'; // Explicitly define table name if it deviates from plural model name

	protected $fillable = [
		'item_id',
		'location_id',
		'quantity',
	];

	/**
	 * Get the item associated with this stock record.
	 */
	public function item(): BelongsTo
	{
		return $this->belongsTo(Item::class);
	}

	/**
	 * Get the location associated with this stock record.
	 */
	public function location(): BelongsTo
	{
		return $this->belongsTo(Location::class);
	}
}