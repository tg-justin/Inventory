<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('conventions', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->date('start_date');
			$table->date('end_date');
			$table->string('city')->nullable();
			$table->string('state')->nullable(); // Or country if international
			$table->string('venue')->nullable();
			$table->text('notes')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('conventions');
	}
};