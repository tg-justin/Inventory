<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
	use HasFactory;

	protected $fillable = [
		'item_id',
		'quantity',
		'from_location_id',
		'to_location_id',
		'user_id',
		'movement_type',
		'related_entity_type',
		'related_entity_id',
		'notes',
	];

	/**
	 * Get the item involved in the movement.
	 */
	public function item(): BelongsTo
	{
		return $this->belongsTo(Item::class);
	}

	/**
	 * Get the location the item moved from (if applicable).
	 */
	public function fromLocation(): BelongsTo
	{
		return $this->belongsTo(Location::class, 'from_location_id');
	}

	/**
	 * Get the location the item moved to (if applicable).
	 */
	public function toLocation(): BelongsTo
	{
		return $this->belongsTo(Location::class, 'to_location_id');
	}

	/**
	 * Get the user who initiated the movement (if applicable).
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the parent related model (ConventionRequest, PurchaseOrder, etc.).
	 * Defines the polymorphic relationship.
	 */
	public function relatedEntity(): MorphTo
	{
		return $this->morphTo();
	}
}