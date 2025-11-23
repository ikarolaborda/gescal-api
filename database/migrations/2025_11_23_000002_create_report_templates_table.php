<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('entity_type', ['persons', 'families', 'cases', 'benefits']);
            $table->json('configuration');
            $table->boolean('is_shared')->default(false);
            $table->foreignId('organization_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->index('user_id');
            $table->index(['is_shared', 'organization_id']);
            $table->index('entity_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
