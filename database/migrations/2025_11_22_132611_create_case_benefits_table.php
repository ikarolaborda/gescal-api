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
        Schema::create('case_benefits', function (Blueprint $table) {
            $table->foreignId('case_id')
                ->constrained('cases')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('benefit_id')
                ->constrained('benefits')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->primary(['case_id', 'benefit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_benefits');
    }
};
