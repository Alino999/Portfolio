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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('summary')->nullable();
            $table->longText('bio')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('country')->nullable();
            $table->string('availability')->nullable();
            $table->string('professional_status')->nullable();
            $table->integer('years_experience')->default(0);
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->json('custom_titles')->nullable();
            $table->json('primary_skills')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('label')->nullable();
            $table->string('url');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('position');
            $table->string('location')->nullable();
            $table->string('contract_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->json('description')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('achievements')->nullable();
            $table->json('technologies')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('school');
            $table->string('degree');
            $table->string('field')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('name');
            $table->string('level')->nullable();
            $table->unsignedTinyInteger('percentage')->nullable();
            $table->integer('years_experience')->default(0);
            $table->json('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('issuer')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('url')->nullable();
            $table->json('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('project_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->string('image_path')->nullable();
            $table->json('gallery')->nullable();
            $table->date('project_date')->nullable();
            $table->string('client')->nullable();
            $table->string('role')->nullable();
            $table->json('technologies')->nullable();
            $table->json('features')->nullable();
            $table->json('results')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->string('status')->default('published');
            $table->boolean('is_confidential')->default(false);
            $table->string('project_type')->default('professional');
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('locale')->nullable();
            $table->string('level')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('role')->nullable();
            $table->string('company')->nullable();
            $table->json('content');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('excerpt')->nullable();
            $table->json('content');
            $table->string('image_path')->nullable();
            $table->string('author_name')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('status')->default('draft');
            $table->integer('sort_order')->default(0);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size')->default(0);
            $table->string('category')->default('general');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key');
            $table->string('locale');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['group', 'key', 'locale']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('title');
            $table->boolean('enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->json('label');
            $table->string('url');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('resume_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('layout')->default('classic');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('locale')->default('fr');
            $table->foreignId('resume_template_id')->constrained()->cascadeOnDelete();
            $table->string('length')->default('standard');
            $table->string('format')->default('pdf');
            $table->json('sections')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('seo_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->json('title')->nullable();
            $table->json('description')->nullable();
            $table->json('keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_image')->nullable();
            $table->string('robots')->default('index,follow');
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('seo_metadata');
        Schema::dropIfExists('resumes');
        Schema::dropIfExists('resume_templates');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('media');
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('article_categories');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('technologies');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_categories');
        Schema::dropIfExists('services');
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('profiles');
    }
};
