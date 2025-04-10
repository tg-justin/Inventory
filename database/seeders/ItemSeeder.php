<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log; // For logging errors

class ItemSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// --- Fetch Required Categories ---
		// Use firstOrFail() to stop seeding if essential categories are missing,
		// or first() and check with logging for less critical ones.
		try {
			$catButtonExtra = Category::where('name', 'Button, Extra')->firstOrFail();
			$catPinExtra = Category::where('name', 'Enamel Pin, Extra')->firstOrFail();
			$catRibbonCore = Category::where('name', 'Ribbons, Core')->firstOrFail();
			$catRibbonPronoun = Category::where('name', 'Ribbons, Pronoun')->firstOrFail();
			$catWristband = Category::where('name', 'Wristbands')->first(); // Example of non-critical
			$catBadgeHolder = Category::where('name', 'Badge Holder')->first();

		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			Log::error("ItemSeeder failed: Could not find essential categories. Ensure CategorySeeder ran first.");
			$this->command->error("ItemSeeder failed: Could not find essential categories. Ensure CategorySeeder ran first.");
			return; // Stop seeding items
		}

		// --- Seed Specific Items ---

		// Buttons
		Item::factory()->forCategory($catButtonExtra)->withSku('BU', 'ALLY', 'Ally Button')->create([
																										'cost_per_unit' => 0.25, 'value_per_unit' => null, 'unit' => 'piece' // Use 'piece' if sold individually
																									]);
		Item::factory()->forCategory($catButtonExtra)->withSku('BU', 'GYMR', 'Gaymer Button')->create([
																										  'cost_per_unit' => 0.25, 'value_per_unit' => null, 'unit' => 'piece'
																									  ]);
		Item::factory()->forCategory($catButtonExtra)->withSku('BU', 'STY', 'Support Trans Youth Button')->create([
																													  'cost_per_unit' => 0.23, 'value_per_unit' => null, 'unit' => 'piece'
																												  ]);

		// Enamel Pins
		Item::factory()->forCategory($catPinExtra)->withSku('EP', 'KITT', 'TG Kitten Pin')->create([
																									   'cost_per_unit' => 0.98, 'value_per_unit' => 5.00, 'unit' => 'piece' // Example value
																								   ]);
		Item::factory()->forCategory($catPinExtra)->withSku('EP', 'PUPP', 'TG Puppers Pin')->create([
																										'cost_per_unit' => 0.98, 'value_per_unit' => 5.00, 'unit' => 'piece'
																									]);
		Item::factory()->forCategory($catPinExtra)->withSku('EP', 'SIGO', 'TG Significant Otters Pin')->create([
																												   'cost_per_unit' => 2.55, 'value_per_unit' => 8.00, 'unit' => 'piece'
																											   ]);
		Item::factory()->forCategory($catPinExtra)->withSku('EP', 'DRAG', 'TG Pride Dragon Pin')->create([
																											 'cost_per_unit' => 0.98, 'value_per_unit' => 8.00, 'unit' => 'piece'
																										 ]);

		// Ribbons
		Item::factory()->forCategory($catRibbonCore)->withSku('RC', 'ALLY', 'Ally Ribbon')->create([
																									   'cost_per_unit' => 0.09, 'value_per_unit' => null, 'unit' => 'piece'
																								   ]);
		Item::factory()->forCategory($catRibbonCore)->withSku('RC', 'GYMR', 'Gaymer Ribbon')->create([
																										 'cost_per_unit' => 0.09, 'value_per_unit' => null, 'unit' => 'piece'
																									 ]);
		Item::factory()->forCategory($catRibbonPronoun)->withSku('RP', 'HE', 'He/Him Ribbon')->create([
																										  'cost_per_unit' => 0.09, 'value_per_unit' => null, 'unit' => 'piece'
																									  ]);
		Item::factory()->forCategory($catRibbonPronoun)->withSku('RP', 'SHE', 'She/Her Ribbon')->create([
																											'cost_per_unit' => 0.09, 'value_per_unit' => null, 'unit' => 'piece'
																										]);
		Item::factory()->forCategory($catRibbonPronoun)->withSku('RP', 'THEY', 'They/Them Ribbon')->create([
																											   'cost_per_unit' => 0.09, 'value_per_unit' => null, 'unit' => 'piece'
																										   ]);

		// --- Seed Random Items in Other Categories (if category exists) ---
		if ($catWristband) {
			Item::factory(3)->forCategory($catWristband)->create(['unit' => 'piece']);
		}
		if ($catBadgeHolder) {
			Item::factory(1)->forCategory($catBadgeHolder)->withSku('BH','HLDR', 'Badge Holder')->create(['unit' => 'indv']);
		}

		// Add more specific or random items as needed...

		$this->command->info('ItemSeeder completed.');
	}
}