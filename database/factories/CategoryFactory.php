<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category; // Import Category

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'name' => fake()->unique()->words(fake()->numberBetween(1, 3), true), // e.g., "Buttons", "Enamel Pins", "Ribbons Core"
			'parent_id' => null, // Default to top-level category
			'sort_order' => fake()->numberBetween(0, 100),
		];
	}

	/**
	 * Define a state for a sub-category.
	 * Requires passing either a parent Category object or parent ID.
	 */
	public function subCategory(Category|int $parent): static
	{
		return $this->state(fn (array $attributes) => [
			// Example naming: "Parent Name - Child Name" or just the child name
			// 'name' => (is_object($parent) ? $parent->name : Category::find($parent)->name) . ' - ' . fake()->words(1, true),
			'name' => fake()->words(fake()->numberBetween(1, 2), true) . ' ' . fake()->randomElement(['Extra', 'Special', 'Admin', 'Set']), // e.g., "Button Extra", "Ribbon Special"
			'parent_id' => is_object($parent) ? $parent->id : $parent,
		]);
	}
}