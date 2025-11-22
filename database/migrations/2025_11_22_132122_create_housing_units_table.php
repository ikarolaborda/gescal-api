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
        Schema::create('housing_units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_id')
                ->constrained('families')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->enum('housing_situation', ['PROPRIA', 'ALUGADA', 'CEDIDA', 'OCCUPIED', 'OTHER'])->nullable();
            $table->enum('construction_type', ['ALVENARIA', 'MADEIRA', 'MISTA', 'OTHER'])->nullable();
            $table->unsignedInteger('room_count')->nullable();
            $table->decimal('rent_or_financing_value', 10, 2)->nullable();
            $table->boolean('participates_housing_program')->default(false);
            $table->string('housing_program_name', 150)->nullable();
            $table->string('housing_program_process', 100)->nullable();
            $table->integer('length_of_residence_months')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_units');
    }
};
