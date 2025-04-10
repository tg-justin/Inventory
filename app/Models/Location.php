<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'is_primary_storage',
		'is_personal_cache',
		'address',
		'notes',
	];

	/**
	 * Get the inventory stock records for this location.
	 */
	public function inventoryStock(): HasMany
	{
		return $this->hasMany(InventoryStock::class);
	}

	/**
	 * Get the inventory movements originating from this location.
	 */
	public function outgoingMovements(): HasMany
	{
		return $this->hasMany(InventoryMovement::class, 'from_location_id');
	}

	/**
	 * Get the inventory movements arriving at this location.
	 */
	public function incomingMovements(): HasMany
	{
		return $this->hasMany(InventoryMovement::class, 'to_location_id');
	}

	/**
	 * Get the convention reconciliations where items were returned TO this location.
	 */
	public function conventionReturns(): HasMany
	{
		return $this->hasMany(ConventionReconciliation::class, 'return_location_id');
	}

	/**
	 * Get the convention reconciliations where items were kept BY a requester AT this location.
	 */
	public function requesterKeptItems(): HasMany
	{
		return $this->hasMany(ConventionReconciliation::class, 'requester_kept_location_id');
	}
}