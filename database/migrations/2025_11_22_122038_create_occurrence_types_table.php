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
        Schema::create('occurrence_types', function (Blueprint $table) {
            $table->id();
            $table->enum('occurrence_type', [
                'incendio',
                'deslizamento',
                'inundação',
                'risco_de_incendio',
                'risco_de_deslizamento',
                'risco_de_inundação',
                'desabamento',
                'destelhamento',
                'queda_de_muro',
                'queda_de_arvore',
                'queda_de_edificio',
                'queda_de_ponte',
                'enxurrada',
                'terremoto',
                'outro',
                'nao_declarado'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occurrence_types');
    }
};
