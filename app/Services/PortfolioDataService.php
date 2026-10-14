<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Media;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Section;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\Testimonial;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PortfolioDataService
{
    protected function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    protected function safeCollection(callable $queryBuilder): Collection
    {
        try {
            return $queryBuilder();
        } catch (QueryException) {
            return collect();
        }
    }

    public function siteSettings(): Collection
    {
        if (! $this->tableExists('site_settings')) {
            return collect();
        }

        return SiteSetting::query()->get()->mapWithKeys(fn ($setting) => [$setting->key => $setting->value]);
    }

    public function profile(): ?Profile
    {
        if (! $this->tableExists('profiles')) {
            return null;
        }

        $profile = Profile::query()->with(['socialLinks', 'experiences', 'educations', 'professionalReferences', 'achievements'])->first();

        if ($profile && empty($profile->avatar_path)) {
            $brandingFiles = collect(Storage::disk('public')->allFiles('branding'))
                ->filter(fn (string $path) => preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $path))
                ->values();

            if ($brandingFiles->isNotEmpty()) {
                $profile->avatar_path = $brandingFiles->first();
            }
        }

        return $profile;
    }

    public function sections(): Collection
    {
        if (! $this->tableExists('sections')) {
            return collect();
        }

        $configuredSections = Section::query()->orderBy('sort_order')->get();
        if ($configuredSections->isNotEmpty()) {
            return $configuredSections->where('enabled', true)->values();
        }

        return collect([
            ['key' => 'about', 'title' => ['fr' => 'À propos', 'en' => 'About'], 'sort_order' => 1],
            ['key' => 'skills', 'title' => ['fr' => 'Compétences', 'en' => 'Skills'], 'sort_order' => 2],
            ['key' => 'experience', 'title' => ['fr' => 'Expérience', 'en' => 'Experience'], 'sort_order' => 3],
            ['key' => 'services', 'title' => ['fr' => 'Services', 'en' => 'Services'], 'sort_order' => 4],
            ['key' => 'projects', 'title' => ['fr' => 'Projets', 'en' => 'Projects'], 'sort_order' => 5],
            ['key' => 'certifications', 'title' => ['fr' => 'Certifications', 'en' => 'Certifications'], 'sort_order' => 6],
            ['key' => 'blog', 'title' => ['fr' => 'Blog', 'en' => 'Blog'], 'sort_order' => 7],
            ['key' => 'testimonials', 'title' => ['fr' => 'Témoignages', 'en' => 'Testimonials'], 'sort_order' => 8],
        ])->map(fn (array $section) => new Section($section));
    }

    public function skills(): Collection
    {
        return $this->safeCollection(fn () => Skill::query()->with('category')->where('published', true)->orderBy('sort_order')->get());
    }

    public function experiences(): Collection
    {
        return $this->safeCollection(fn () => Experience::query()->where('published', true)->orderBy('sort_order')->get());
    }

    public function projects(): Collection
    {
        return $this->safeCollection(fn () => Project::query()->where('published', true)->orderBy('sort_order')->get());
    }

    public function services(): Collection
    {
        return $this->safeCollection(fn () => Service::query()->where('published', true)->orderBy('sort_order')->get());
    }

    public function certifications(): Collection
    {
        return $this->safeCollection(fn () => Certification::query()->where('published', true)->orderBy('sort_order')->get());
    }

    public function technologies(): Collection
    {
        return $this->safeCollection(fn () => Technology::query()->where('published', true)->orderBy('sort_order')->get());
    }

    public function languages(): Collection
    {
        return $this->safeCollection(fn () => Language::query()->where('published', true)->orderBy('sort_order')->get());
    }

    public function testimonials(): Collection
    {
        return $this->safeCollection(fn () => Testimonial::query()->where('published', true)->orderBy('sort_order')->get());
    }

    public function articles(?string $visitorHash = null): Collection
    {
        return $this->safeCollection(fn () => Article::query()
            ->withCount('likes')
            ->with(['comments' => fn ($query) => $query->where('status', 'approved')->latest()])
            ->when($visitorHash !== null, fn ($query) => $query->with(['likes' => fn ($likes) => $likes->where('visitor_hash', $visitorHash)]))
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get());
    }

    public function homepageSlides(): Collection
    {
        $slides = $this->safeCollection(fn () => Media::query()
            ->where('category', 'homepage_slider')
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get());

        if ($slides->isNotEmpty()) {
            return $slides;
        }

        return collect(Storage::disk('public')->allFiles('homepage-slides'))
            ->filter(fn (string $path) => preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $path))
            ->values()
            ->map(fn (string $path) => (object) [
                'file_path' => $path,
                'alt_text' => pathinfo($path, PATHINFO_FILENAME),
                'title' => pathinfo($path, PATHINFO_FILENAME),
                'is_public' => true,
            ]);
    }
}
