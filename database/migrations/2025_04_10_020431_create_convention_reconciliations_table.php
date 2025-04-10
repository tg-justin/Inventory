<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('convention_reconciliations', function (Blueprint $table) {
			$table->id();
			$table->foreignId('convention_request_id')->constrained()->onDelete('cascade'); // Link back to the request
			$table->foreignId('item_id')->constrained()->onDelete('restrict'); // Link to the specific item being reconciled
			$table->integer('quantity_shipped')->comment('Copied from request item for reference');
			$table->integer('quantity_used')->default(0)->comment('Given away, sold, donated');
			$table->integer('quantity_returned_to_storage')->default(0);
			$table->foreignId('return_location_id')->nullable()->constrained('locations')->onDelete('set null'); // Where did returns go?
			$table->integer('quantity_kept_by_requester')->default(0);
			$table->foreignId('requester_kept_location_id')->nullable()->constrained('locations')->onDelete('set null'); // Which cache did requester keep items in?
			$table->integer('quantity_lost_damaged')->default(0);
			$table->date('reconciliation_date');
			$table->foreignId('reconciler_id')->nullable()->constrained('users')->onDelete('set null'); // Who submitted this reconciliation?
			$table->text('notes')->nullable();
			$table->timestamps();

			// Prevent duplicate reconciliation entry for the same item on the same request
			$table->unique(['convention_request_id', 'item_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('convention_reconciliations');
	}
};