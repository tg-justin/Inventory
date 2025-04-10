<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('inventory_movements', function (Blueprint $table) {
			$table->id();
			$table->foreignId('item_id')->constrained()->onDelete('restrict'); // Don't delete movements if item deleted (restrict item deletion instead)
			$table->integer('quantity'); // Positive for additions, negative for removals
			$table->foreignId('from_location_id')->nullable()->constrained('locations')->onDelete('set null'); // Location moved FROM
			$table->foreignId('to_location_id')->nullable()->constrained('locations')->onDelete('set null'); // Location moved TO
			$table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // User performing action
			$table->string('movement_type'); // e.g., 'purchase', 'transfer', 'adjustment', 'convention_shipment', 'convention_usage', 'convention_return'
			$table->string('related_entity_type')->nullable(); // e.g., 'App\Models\ConventionRequest'
			$table->unsignedBigInteger('related_entity_id')->nullable(); // ID of the related entity
			$table->text('notes')->nullable();
			$table->timestamps(); // Records when movement happened

			$table->index(['related_entity_type', 'related_entity_id']); // Index for polymorphic relation
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('inventory_movements');
	}
};