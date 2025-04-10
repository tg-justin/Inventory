<?php

namespace Database\Seeders;

use App\Models\Convention;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConventionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Create some specific past conventions
		Convention::factory()->past()->create([
												  'name' => 'Gen Con 2023',
												  'city' => 'Indianapolis',
												  'state' => 'IN',
												  'start_date' => '2023-08-03',
												  'end_date' => '2023-08-06',
											  ]);
		Convention::factory()->past()->create([
												  'name' => 'PAX Unplugged 2023',
												  'city' => 'Philadelphia',
												  'state' => 'PA',
												  'start_date' => '2023-12-01',
												  'end_date' => '2023-12-03',
											  ]);

		// Create some specific future conventions
		Convention::factory()->future()->create([
													'name' => 'PAX East 2025', // Adjusted year for future example
													'city' => 'Boston',
													'state' => 'MA',
													'start_date' => '2025-03-21',
													'end_date' => '2025-03-24',
												]);
		Convention::factory()->future()->create([
													'name' => 'Origins Game Fair 2025', // Adjusted year
													'city' => 'Columbus',
													'state' => 'OH',
													'start_date' => '2025-06-19',
													'end_date' => '2025-06-22',
												]);

		// Create a few random future conventions
		Convention::factory(3)->future()->create();

		// Create a few random past conventions
		Convention::factory(2)->past()->create();

		$this->command->info('ConventionSeeder completed.');
	}
}