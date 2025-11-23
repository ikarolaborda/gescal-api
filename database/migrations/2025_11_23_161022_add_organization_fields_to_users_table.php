<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained('organizations')
                ->nullOnDelete();

            $table->string('status', 20)
                ->default('pending')
                ->after('password');

            $table->text('rejection_reason')
                ->nullable()
                ->after('status');

            $table->string('cancellation_token', 64)
                ->nullable()
                ->unique()
                ->after('rejection_reason');

            $table->timestamp('cancellation_token_expires_at')
                ->nullable()
                ->after('cancellation_token');

            // Indexes
            $table->index('organization_id');
            $table->index('status');
            $table->index(['cancellation_token', 'status']);
        });

        // Update existing users to have active status
        DB::table('users')->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn([
                'organization_id',
                'status',
                'rejection_reason',
                'cancellation_token',
                'cancellation_token_expires_at',
            ]);
        });
    }
};
