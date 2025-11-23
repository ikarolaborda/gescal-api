<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
                'avo',
                'avo_feminino',
                'tio',
                'tia',
                'sobrinho',
                'sobrinha',
                'primo',
                'prima',
                'sogro',
                'sogra',
                'genro',
                'nora',
                'esposa',
                'marido',
                'filho_adotivo',
                'filha_adotiva',
                'neto',
                'neta',
                'bisneto',
                'bisneta',
                'nao_declarado',
                'outro',
            ]);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kinships');
    }
};
