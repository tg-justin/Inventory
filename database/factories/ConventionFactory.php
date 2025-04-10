<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon; // Import Carbon

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Convention>
 */
class ConventionFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		$startDate = Carbon::instance(fake()->dateTimeBetween('-2 years', '+1 year'));
		$endDate = $startDate->copy()->addDays(fake()->numberBetween(1, 4)); // Ensure end is after start

		return [
			'name' => fake()->randomElement(['Gen Con', 'PAX Unplugged', 'Origins', 'PAX East', 'Local Con']) . ' ' . $startDate->year,
			'start_date' => $startDate,
			'end_date' => $endDate,
			'city' => fake()->city(),
			'state' => fake()->stateAbbr(),
			'venue' => fake()->optional()->company() . ' Convention Center',
			'notes' => fake()->optional()->sentence(),
		];
	}

	/**
	 * Indicate that the convention is in the future.
	 */
	public function future(): static
	{
		return $this->state(function (array $attributes) {
			$startDate = Carbon::instance(fake()->dateTimeBetween('+2 months', '+1 year')); // Start at least 2 months out
			$endDate = $startDate->copy()->addDays(fake()->numberBetween(1, 4));
			return [
				'start_date' => $startDate,
				'end_date' => $endDate,
				// Adjust name to reflect future year if needed, or keep random year from definition
				'name' => fake()->randomElement(['Gen Con', 'PAX Unplugged', 'Origins', 'PAX East', 'Future Con']) . ' ' . $startDate->year,
			];
		});
	}

	/**
	 * Indicate that the convention is in the past.
	 */
	public function past(): static
	{
		return $this->state(function (array $attributes) {
			$startDate = Carbon::instance(fake()->dateTimeBetween('-3 years', '-3 months')); // Ensure it ended at least 3 months ago
			$endDate = $startDate->copy()->addDays(fake()->numberBetween(1, 4));
			return [
				'start_date' => $startDate,
				'end_date' => $endDate,
				'name' => fake()->randomElement(['Gen Con', 'PAX Unplugged', 'Origins', 'PAX East', 'Past Con']) . ' ' . $startDate->year,
			];
		});
	}
}