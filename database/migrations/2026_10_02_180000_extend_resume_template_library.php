<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('resume_templates', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('resume_template_categories')->nullOnDelete();
            $table->string('style')->nullable();
            $table->boolean('ats_friendly')->default(false);
            $table->json('languages')->nullable();
            $table->string('recommended_pages')->default('two_pages');
            $table->json('available_formats')->nullable();
            $table->json('available_colors')->nullable();
            $table->json('blocks')->nullable();
            $table->string('preview_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::table('resumes', function (Blueprint $table) {
            $table->json('content_selection')->nullable();
            $table->json('customization')->nullable();
            $table->string('target_role')->nullable();
            $table->string('target_market')->nullable();
            $table->text('summary_override')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropColumn([
                'content_selection',
                'customization',
                'target_role',
                'target_market',
                'summary_override',
            ]);
        });

        Schema::table('resume_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'style',
                'ats_friendly',
                'languages',
                'recommended_pages',
                'available_formats',
                'available_colors',
                'blocks',
                'preview_image',
                'is_active',
                'is_system',
                'sort_order',
            ]);
        });

        Schema::dropIfExists('resume_template_categories');
    }
};
