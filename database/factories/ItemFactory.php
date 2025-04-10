<?php

namespace Database\Factories;

use App\Models\Category; // Import Category
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // Import Str

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		$name = fake()->unique()->words(fake()->numberBetween(2, 4), true); // e.g., "Awesome Swag Item Alpha"
		return [
			'name' => $name,
			'description' => fake()->optional()->sentence(),
			// Generate a plausible SKU based on category and name
			'sku' => 'FKE_' . Str::slug(substr($name, 0, 15), '_'), // Example: FKE_awesome_swag_it
			// Assign to a random existing category, or you can specify one when calling the factory
			'category_id' => Category::query()->inRandomOrder()->first()?->id ?? Category::factory(),
			'unit' => fake()->randomElement(['piece', 'bag', 'box', 'roll', 'kit', 'pack']),
			'cost_per_unit' => fake()->randomFloat(2, 0.1, 5.0), // Random cost between 0.10 and 5.00
			'value_per_unit' => fake()->optional(0.7)->randomFloat(2, 0.5, 10.0), // 70% chance of having a value
			'reorder_threshold' => fake()->optional(0.5)->numberBetween(10, 100), // 50% chance of having a threshold
			'image_path' => null,
			'is_active' => true,
			'notes' => fake()->optional()->paragraph(1),
		];
	}

	/**
	 * Assign item to a specific category
	 */
	public function forCategory(Category|int $category): static
	{
		return $this->state(fn (array $attributes) => [
			'category_id' => is_object($category) ? $category->id : $category,
		]);
	}

	/**
	 * Generate SKU based on specific category prefix and item name/code
	 * e.g., ->withSku('BU', 'ALLY') -> BU_ALLY
	 */
	public function withSku(string $categoryPrefix, string $itemCode): static
	{
		return $this->state(fn (array $attributes) => [
			'sku' => strtoupper($categoryPrefix) . '_' . strtoupper($itemCode),
		]);
	}
}