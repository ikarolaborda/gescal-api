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
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_id')
                ->nullable()
                ->constrained('families')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('person_id')
                ->nullable()
                ->constrained('persons')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('benefit_program_id')
                ->constrained('benefit_programs')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('value', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefits');
    }
};
