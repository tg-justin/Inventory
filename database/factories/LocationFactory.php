<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		// Default state is likely a personal cache or generic location
		return [
			'name' => fake()->unique()->words(2, true) . ' Cache', // e.g., "North Cache", "West Storage"
			'is_primary_storage' => false,
			'is_personal_cache' => true,
			'address' => fake()->optional()->address(), // Make address optional
			'notes' => fake()->optional()->sentence(),
		];
	}

	/**
	 * Define a state for the primary storage location.
	 */
	public function primaryStorage(): static
	{
		return $this->state(fn (array $attributes) => [
			'name' => 'Main Storage', // Specific name
			'is_primary_storage' => true,
			'is_personal_cache' => false,
			// Could add a more specific address here if desired
		]);
	}

	/**
	 * Define a state for a named personal cache.
	 */
	public function personalCache(string $name): static
	{
		return $this->state(fn (array $attributes) => [
			'name' => $name . "'s Cache", // Specific name structure
			'is_primary_storage' => false,
			'is_personal_cache' => true,
		]);
	}
}