<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_execution_histories', function (Blueprint $table) {
            // Add started_at and completed_at timestamps
            $table->timestamp('started_at')->nullable()->after('error_message');
            $table->timestamp('completed_at')->nullable()->after('started_at');

            // Modify status enum to include 'processing'
            $table->enum('status', ['processing', 'completed', 'failed'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_execution_histories', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['started_at', 'completed_at']);

            // Revert status enum to original values
            $table->enum('status', ['completed', 'failed'])->change();
        });
    }
};
