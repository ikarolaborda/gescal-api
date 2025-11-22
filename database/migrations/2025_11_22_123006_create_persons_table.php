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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 255);
            $table->enum('sex', ['Masculino', 'Feminino'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('filiation_text', 255)->nullable();
            $table->string('nationality', 100)->nullable()->default('brasileiro');
            $table->string('natural_city', 150)->nullable();
            $table->foreignId('natural_federation_unit_id')
                ->constrained('federation_units')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('race_ethnicity_id')
                ->nullable()
                ->constrained('race_ethnicities')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('marital_status_id')
                ->nullable()
                ->constrained('marital_statuses')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('schooling_level_id')
                ->nullable()
                ->constrained('schooling_levels')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('primary_phone', 30)->nullable();
            $table->string('secondary_phone', 30)->nullable();
            $table->string('email', 150)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
