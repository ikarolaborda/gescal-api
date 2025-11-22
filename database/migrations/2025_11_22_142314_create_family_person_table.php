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
        Schema::create('family_person', function (Blueprint $table) {
            $table->foreignId('family_id')
                ->constrained('families')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('person_id')
                ->constrained('persons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('kinship_id')
                ->nullable()
                ->constrained('kinships')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('is_responsible')->default(false);
            $table->boolean('lives_in_household')->default(true);

            $table->primary(['family_id', 'person_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_person');
    }
};
