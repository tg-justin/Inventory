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
}