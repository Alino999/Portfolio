<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('subtitle_en')->nullable()->after('subtitle');
            $table->text('summary_en')->nullable()->after('summary');
            $table->longText('bio_en')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'subtitle_en', 'summary_en', 'bio_en']);
        });
    }
};