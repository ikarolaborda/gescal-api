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
        // Add LGPD compliance fields to persons table
        Schema::table('persons', function (Blueprint $table) {
            $table->boolean('data_processing_consent')->default(false)->after('updated_at');
            $table->timestamp('consent_given_at')->nullable()->after('data_processing_consent');
            $table->timestamp('consent_withdrawn_at')->nullable()->after('consent_given_at');
            $table->text('consent_meta')->nullable()->after('consent_withdrawn_at');
        });

        // Add LGPD compliance fields to families table
        Schema::table('families', function (Blueprint $table) {
            $table->boolean('data_processing_consent')->default(false)->after('updated_at');
            $table->timestamp('consent_given_at')->nullable()->after('data_processing_consent');
            $table->timestamp('consent_withdrawn_at')->nullable()->after('consent_given_at');
            $table->text('consent_meta')->nullable()->after('consent_withdrawn_at');
        });

        // Add audit markers to sensitive tables
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(true)->after('updated_at');
            $table->string('encryption_key_version')->nullable()->after('is_sensitive');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(true)->after('updated_at');
            $table->string('encryption_key_version')->nullable()->after('is_sensitive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn([
                'data_processing_consent',
                'consent_given_at',
                'consent_withdrawn_at',
                'consent_meta',
            ]);
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn([
                'data_processing_consent',
                'consent_given_at',
                'consent_withdrawn_at',
                'consent_meta',
            ]);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'is_sensitive',
                'encryption_key_version',
            ]);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'is_sensitive',
                'encryption_key_version',
            ]);
        });
    }
};
