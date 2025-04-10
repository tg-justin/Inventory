<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('convention_requests', function (Blueprint $table) {
			$table->id();
			$table->foreignId('convention_id')->constrained()->onDelete('cascade'); // If convention deleted, delete requests
			$table->foreignId('requester_id')->nullable()->constrained('users')->onDelete('set null'); // If user deleted, keep request but nullify requester
			$table->date('request_date');
			$table->date('needed_by_date');
			$table->string('status')->default(App\Enums\ConventionRequestStatus::Draft->value);
			$table->text('shipping_address')->nullable();
			$table->string('shipping_tracking_number')->nullable();
			$table->text('notes')->nullable(); // Requester notes
			$table->text('admin_notes')->nullable(); // Internal/Admin notes
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('convention_requests');
	}
};