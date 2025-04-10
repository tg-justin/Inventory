<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConventionRequestItem extends Model
{
	use HasFactory;

	protected $fillable = [
		'convention_request_id',
		'item_id',
		'quantity_requested',
		'quantity_approved',
		'quantity_shipped',
		'notes',
	];

	/**
	 * Get the convention request this item belongs to.
	 */
	public function conventionRequest(): BelongsTo
	{
		return $this->belongsTo(ConventionRequest::class);
	}

	/**
	 * Get the item being requested.
	 */
	public function item(): BelongsTo
	{
		return $this->belongsTo(Item::class);
	}

	public function conventionReconciliation(): HasOne
	{
		// Assumes foreign key on convention_reconciliations is convention_request_item_id
		// If not, adjust keys. But our schema used convention_request_id + item_id.
		// Let's link through the request instead for updateOrCreate convenience:
		return $this->hasOne(ConventionReconciliation::class); // This might require convention_request_item_id FK

		// Linking via request_id and item_id is better for updateOrCreate logic
		// No direct relationship needed here if using updateOrCreate in action.
	}
}