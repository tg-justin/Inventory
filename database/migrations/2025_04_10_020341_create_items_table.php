<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('items', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->text('description')->nullable();
			$table->string('sku')->unique(); // Use ProdID here
			$table->foreignId('category_id')->nullable()->constrained()->onDelete('set null'); // Foreign key to categories
			$table->string('unit')->default('piece'); // e.g., piece, bag, box
			$table->decimal('cost_per_unit', 8, 2)->nullable();
			$table->decimal('value_per_unit', 8, 2)->nullable();
			$table->integer('reorder_threshold')->nullable();
			$table->string('image_path')->nullable();
			$table->boolean('is_active')->default(TRUE);
			$table->text('notes')->nullable();
			$table->timestamps(); // Adds created_at and updated_at
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('items');
	}
};