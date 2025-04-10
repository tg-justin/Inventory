<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->string('microsoft_id')->nullable()->unique()->after('email'); // Add the column for SSO ID
			$table->string('password')->nullable()->change(); // Make existing password column nullable
		});
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			// If password was not nullable before, revert it.
			// Check your original users table migration if unsure.
			// This assumes it was originally NOT nullable.
			$table->string('password')->nullable(FALSE)->change();
			$table->dropColumn('microsoft_id');
		});
	}
};