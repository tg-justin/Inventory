<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConventionReconciliation extends Model
{
	use HasFactory;

	protected $fillable = [
		'convention_request_id',
		'item_id',
		'quantity_shipped',
		'quantity_used',
		'quantity_returned_to_storage',
		'return_location_id',
		'quantity_kept_by_requester',
		'requester_kept_location_id',
		'quantity_lost_damaged',
		'reconciliation_date',
		'reconciler_id',
		'notes',
	];

	protected $casts = [
		'reconciliation_date' => 'date',
	];

	/**
	 * Get the convention request being reconciled.
	 */
	public function conventionRequest(): BelongsTo
	{
		return $this->belongsTo(ConventionRequest::class);
	}

	/**
	 * Get the item being reconciled.
	 */
	public function item(): BelongsTo
	{
		return $this->belongsTo(Item::class);
	}

	/**
	 * Get the location where items were returned.
	 */
	public function returnLocation(): BelongsTo
	{
		return $this->belongsTo(Location::class, 'return_location_id');
	}

	/**
	 * Get the location (cache) where the requester kept items.
	 */
	public function requesterKeptLocation(): BelongsTo
	{
		return $this->belongsTo(Location::class, 'requester_kept_location_id');
	}

	/**
	 * Get the user who performed the reconciliation.
	 */
	public function reconciler(): BelongsTo
	{
		return $this->belongsTo(User::class, 'reconciler_id');
	}
}