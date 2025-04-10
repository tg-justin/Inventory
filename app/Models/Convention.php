<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Convention extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'start_date',
		'end_date',
		'city',
		'state',
		'venue',
		'notes',
	];

	protected $casts = [
		'start_date' => 'date', // Automatically cast to Carbon date objects
		'end_date' => 'date',
	];

	/**
	 * Get the requests associated with this convention.
	 */
	public function conventionRequests(): HasMany
	{
		return $this->hasMany(ConventionRequest::class);
	}
}