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
        Schema::create('families', function (Blueprint $table) {
            $table->id();

            $table->foreignId('responsible_person_id')
                ->constrained('persons')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('address_id')
                ->nullable()
                ->constrained('addresses')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('origin_city', 150)->nullable();
            $table->foreignId('origin_federation_unit_id')
                ->constrained('federation_units')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('family_income_bracket', 100)->nullable();
            $table->decimal('family_income_value', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
