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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('person_id')
                ->constrained('persons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('document_type_id')
                ->constrained('document_types')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('number', 100);
            $table->string('issuing_body', 100)->nullable();
            $table->foreignId('issuing_federation_unit_id')
                ->constrained('federation_units')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->date('issued_at')->nullable();
            $table->boolean('is_primary')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
