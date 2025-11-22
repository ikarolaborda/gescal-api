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
        Schema::create('kinships', function (Blueprint $table) {
            $table->id();
            $table->enum('kinship', [
                'pai',
                'mae',
                'filho',
                'filha',
                'irmao',
                'irma',
                'avô',
                'avó',
                'tio',
                'tia',
                'sobrinho',
                'sobrinha',
                'primo',
                'prima',
                'sogro',
                'sogra',
                'genro',
                'esposa',
                'marido',
                'filho_adotivo',
                'filha_adotiva',
                'neto',
                'neta',
                'bisneto',
                'bisneta',
                'nao_declarado',
                'outro'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kinships');
    }
};
