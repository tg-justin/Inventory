<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Create the main storage using the specific state
		Location::factory()->primaryStorage()->create();

		// Create the specific personal caches using the named state
		Location::factory()->personalCache('Jeff')->create();
		Location::factory()->personalCache('Unai')->create();
		Location::factory()->personalCache('Dan')->create();
		Location::factory()->personalCache('Jessiye')->create();

		// You could create additional random locations for testing if needed
		// Location::factory(3)->create();
	}
}