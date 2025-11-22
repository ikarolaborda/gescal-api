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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('family_id')
                ->constrained('families')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('occurrence_id')
                ->nullable()
                ->constrained('occurrences')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('housing_unit_id')
                ->nullable()
                ->constrained('housing_units')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('dc_number', 50)->nullable()->unique();
            $table->integer('dc_year')->nullable();
            $table->date('service_date');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
