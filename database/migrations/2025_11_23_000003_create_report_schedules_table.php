<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('report_templates')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->time('execution_time');
            $table->tinyInteger('day_of_week')->nullable();
            $table->tinyInteger('day_of_month')->nullable();
            $table->json('recipients');
            $table->json('parameters');
            $table->timestamp('last_execution_at')->nullable();
            $table->timestamp('next_execution_at');
            $table->integer('failure_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->index('user_id');
            $table->index(['next_execution_at', 'is_active']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
