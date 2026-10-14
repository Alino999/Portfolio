<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educations', function (Blueprint $table) {
            $table->string('degree_en')->nullable()->after('degree');
            $table->string('field_en')->nullable()->after('field');
        });

        Schema::table('languages', function (Blueprint $table) {
            $table->string('level_en')->nullable()->after('level');
        });

        Schema::table('professional_references', function (Blueprint $table) {
            $table->string('role_en')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('educations', function (Blueprint $table) {
            $table->dropColumn(['degree_en', 'field_en']);
        });

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('level_en');
        });

        Schema::table('professional_references', function (Blueprint $table) {
            $table->dropColumn('role_en');
        });
    }
};