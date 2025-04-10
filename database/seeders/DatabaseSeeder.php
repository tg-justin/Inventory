<?php

namespace Database\Seeders;

use App\Models\User; // Make sure to import User
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Import Hash

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		// Check if the admin user already exists to avoid errors on re-seeding
		if (!User::where('email', 'admin@example.com')->exists()) {
			User::factory()->create([
										'name' => 'Admin User',
										'email' => 'admin@example.com',
										// Set a specific password you know
										'password' => Hash::make('password'),
										// Add any other fields needed, e.g., role assignment if using spatie/permission
										// 'is_admin' => true, // Or however you designate admins
									]);
		}


		// You can create other random users if needed for testing
		// User::factory(10)->create();


		// We will call other seeders here later
		$this->call([
						LocationSeeder::class,
						CategorySeeder::class, // Must run before ItemSeeder
						ItemSeeder::class, // Runs after CategorySeeder
						ConventionSeeder::class,

					]);
	}
}