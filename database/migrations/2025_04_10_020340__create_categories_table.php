<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('categories', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			// Self-referencing foreign key for parent category
			$table->foreignId('parent_id')
				  ->nullable()
				  ->constrained('categories') // Constraint refers back to the same table
				  ->onDelete('set null'); // If parent deleted, children become top-level
			$table->integer('sort_order')->default(0);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('categories');
	}
};