<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('inventory_stock', function (Blueprint $table) { // Note: Laravel conventions prefer 'inventory_stock' as table name
			$table->id();
			$table->foreignId('item_id')->constrained()->onDelete('cascade'); // If item deleted, stock record is gone
			$table->foreignId('location_id')->constrained()->onDelete('cascade'); // If location deleted, stock record is gone
			$table->integer('quantity')->default(0);
			$table->timestamps(); // Useful for last updated

			// Ensure an item can only exist once per location
			$table->unique(['item_id', 'location_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('inventory_stock');
	}
};