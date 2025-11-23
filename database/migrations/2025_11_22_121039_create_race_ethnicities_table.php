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
        Schema::create('race_ethnicities', function (Blueprint $table) {
            $table->id();
            $table->enum('race_color', [
                'branca',
                'preta',
                'parda',
                'amarela',
                'indigena',
                'nao_declarada',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_ethnicities');
    }
};
