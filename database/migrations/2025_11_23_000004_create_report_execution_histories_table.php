<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_execution_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_schedule_id')
                ->nullable()
                ->constrained('report_schedules')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('report_id')
                ->nullable()
                ->constrained('reports')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->enum('status', ['completed', 'failed']);
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index(['report_schedule_id', 'executed_at']);
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_execution_histories');
    }
};
