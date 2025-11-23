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
        // Add soft deletes to main entity tables
        Schema::table('persons', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('families', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('occurrences', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('housing_units', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('benefits', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('case_social_reports', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('occurrences', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('housing_units', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('benefits', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('case_social_reports', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
