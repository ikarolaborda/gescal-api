<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->enum('entity_type', ['persons', 'families', 'cases', 'benefits']);
            $table->enum('format', ['pdf', 'excel', 'csv', 'json']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'expired']);
            $table->string('file_path')->nullable();
            $table->boolean('file_available')->default(true);
            $table->json('parameters');
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
