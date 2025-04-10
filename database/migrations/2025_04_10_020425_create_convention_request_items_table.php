<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('convention_request_items', function (Blueprint $table) {
			$table->id();
			$table->foreignId('convention_request_id')->constrained()->onDelete('cascade'); // If request deleted, delete line items
			$table->foreignId('item_id')->constrained()->onDelete('restrict'); // Don't allow item deletion if it's on a request
			$table->integer('quantity_requested');
			$table->integer('quantity_approved')->nullable();
			$table->integer('quantity_shipped')->nullable();
			$table->text('notes')->nullable(); // Notes specific to this line item
			$table->timestamps();

			$table->unique(['convention_request_id', 'item_id']); // Prevent adding the same item twice to one request
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('convention_request_items');
	}
};