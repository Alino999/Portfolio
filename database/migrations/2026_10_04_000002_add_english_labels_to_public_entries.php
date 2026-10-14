<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('company_en')->nullable()->after('company');
            $table->string('position_en')->nullable()->after('position');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->string('level_en')->nullable()->after('level');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->string('issuer_en')->nullable()->after('issuer');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['company_en', 'position_en']);
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('level_en');
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropColumn('issuer_en');
        });
    }
};