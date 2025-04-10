<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Create Top-Level Categories
		$buttons = Category::factory()->create(['name' => 'Buttons', 'sort_order' => 100]);
		$enamelPins = Category::factory()->create(['name' => 'Enamel Pins', 'sort_order' => 200]);
		$wristbands = Category::factory()->create(['name' => 'Wristbands', 'sort_order' => 300]);
		$badgeHolders = Category::factory()->create(['name' => 'Badge Holder', 'sort_order' => 400]); // Corrected name based on CSV
		$ribbons = Category::factory()->create(['name' => 'Ribbons', 'sort_order' => 500]); // A general parent for Ribbons?
		$handouts = Category::factory()->create(['name' => 'Handouts', 'sort_order' => 1400]); // Renamed from Info Sheets
		$sponsorTokens = Category::factory()->create(['name' => 'Sponsor Tokens', 'sort_order' => 1500]);
		$cashHandling = Category::factory()->create(['name' => 'Cash Handling', 'sort_order' => 2000]);
		$donations = Category::factory()->create(['name' => 'Donations', 'sort_order' => 2100]);
		$vendor = Category::factory()->create(['name' => 'Vendor In-Kind', 'sort_order' => 2200]);
		$reroll = Category::factory()->create(['name' => 'Reroll Kit', 'sort_order' => 2300]);
		$packing = Category::factory()->create(['name' => 'Packing Materials', 'sort_order' => 3000]);
		$signage = Category::factory()->create(['name' => 'Signage', 'sort_order' => 4000]);
		$misc = Category::factory()->create(['name' => 'Miscellaneous', 'sort_order' => 9000]);

		// Create Sub-Categories using the state and passing the parent
		// Buttons
		Category::factory()->subCategory($buttons)->create(['name' => 'Button, Extra', 'sort_order' => 110]);
		Category::factory()->subCategory($buttons)->create(['name' => 'Button, Special', 'sort_order' => 140]);

		// Enamel Pins
		Category::factory()->subCategory($enamelPins)->create(['name' => 'Enamel Pin, Extra', 'sort_order' => 201]); // Start from 201 based on CSV
		Category::factory()->subCategory($enamelPins)->create(['name' => 'Enamel Pin, Special', 'sort_order' => 240]);
		Category::factory()->subCategory($enamelPins)->create(['name' => 'Enamel Pin, Display', 'sort_order' => 290]); // e.g. Foam board


		// Ribbons (Using the general 'Ribbons' parent for now)
		Category::factory()->subCategory($ribbons)->create(['name' => 'Ribbons, Core', 'sort_order' => 500]); // CSV had same number, maybe adjust
		Category::factory()->subCategory($ribbons)->create(['name' => 'Ribbons, Pronoun', 'sort_order' => 600]); // Can nest further later if needed
		Category::factory()->subCategory($ribbons)->create(['name' => 'Ribbons, Flags', 'sort_order' => 800]);
		Category::factory()->subCategory($ribbons)->create(['name' => 'Ribbon Tray', 'sort_order' => 1100]); // Grouping Empty Trays/Lids
		Category::factory()->subCategory($ribbons)->create(['name' => 'Ribbons, Admin', 'sort_order' => 1200]);
		Category::factory()->subCategory($ribbons)->create(['name' => 'Ribbons, Sponsor', 'sort_order' => 1300]);

		// Handouts
		Category::factory()->subCategory($handouts)->create(['name' => 'Handout, Info Sheet', 'sort_order' => 1400]);
		Category::factory()->subCategory($handouts)->create(['name' => 'Handout, Case', 'sort_order' => 1490]); // Acrylic Rack

		// Add more subcategories as needed based on your spreadsheet structure...
		// e.g., Wristband, Individual; Badge Holder, Extra; etc.
	}
}