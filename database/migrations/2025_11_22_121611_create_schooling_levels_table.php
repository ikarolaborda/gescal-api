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
        Schema::create('schooling_levels', function (Blueprint $table) {
            $table->id();
            $table->enum('schooling_level', [
                'fundamental_incompleto',
                'fundamental_completo',
                'medio_incompleto',
                'medio_completo',
                'superior_incompleto',
                'superior_completo',
                'pos_graduacao_completo',
                'pos_graduacao_incompleto',
                'pos_graduacao_completo',
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
        Schema::dropIfExists('schooling_levels');
    }
};
