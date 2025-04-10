<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('locations', function (Blueprint $table) {
			$table->id();
			$table->string('name')->unique(); // e.g., "Main Storage", "Jeff's Cache"
			$table->boolean('is_primary_storage')->default(false);
			$table->boolean('is_personal_cache')->default(false);
			$table->text('address')->nullable();
			$table->text('notes')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('locations');
	}
};