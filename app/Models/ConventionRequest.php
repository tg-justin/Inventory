<?php

namespace App\Models;

use App\Enums\ConventionRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class ConventionRequest extends Model
{
	use HasFactory;

	protected $fillable = [
		'convention_id',
		'requester_id',
		'request_date',
		'needed_by_date',
		'status',
		'shipping_address',
		'shipping_tracking_number',
		'notes',
		'admin_notes',
	];

	protected $casts = [
		'request_date' => 'date',
		'needed_by_date' => 'date',
		'status' => ConventionRequestStatus::class,
	];

	/**
	 * Get the convention this request is for.
	 */
	public function convention(): BelongsTo
	{
		return $this->belongsTo(Convention::class);
	}

	/**
	 * Get the user who made the request.
	 */
	public function requester(): BelongsTo
	{
		return $this->belongsTo(User::class, 'requester_id');
	}

	/**
	 * Get the items requested for the convention.
	 */
	public function conventionRequestItems(): HasMany
	{
		return $this->hasMany(ConventionRequestItem::class);
	}

	/**
	 * Get the reconciliation records for this request.
	 * Usually there will only be one reconciliation entry per item per request,
	 * but using HasMany allows for potential corrections or multi-stage reconciliation.
	 */
	public function conventionReconciliations(): HasMany
	{
		return $this->hasMany(ConventionReconciliation::class);
	}

	/**
	 * Get all inventory movements associated with this convention request.
	 */
	public function inventoryMovements(): MorphMany
	{
		// Explicitly tell Eloquent the column names used in the migration
		return $this->morphMany(
			InventoryMovement::class, // Related model
			'relatedEntity',          // Relationship name (base for column names)
			'related_entity_type',    // type column name in DB
			'related_entity_id'       // id column name in DB
		);
	}

}