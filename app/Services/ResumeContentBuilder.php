<?php

namespace App\Services;

use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Media;
use App\Models\Project;
use App\Models\ProfessionalReference;
use App\Models\Resume;
use App\Models\Service;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Technology;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ResumeContentBuilder
{
    public function build(Resume $resume): array
    {
        $profile = $resume->profile;
        $locale = $resume->locale;
        $selection = $resume->content_selection;
        $customization = $resume->customization ?? [];
        $templateBlocks = $resume->template?->blocks ?? [];
        $sections = [];
        $avatarPath = null;
        if (($customization['include_photo'] ?? false) && ($resume->template?->supportsPhoto() ?? true)) {
            $selectedPhoto = ! empty($customization['photo_media_id'])
                ? Media::query()->where('category', 'homepage_slider')->where('is_public', true)->where('mime_type', 'image/jpeg')->find($customization['photo_media_id'])
                : null;
            $avatarPath = $selectedPhoto?->file_path
                ?? $profile->avatar_path
                ?? Media::query()->where('category', 'homepage_slider')->where('is_public', true)->where('mime_type', 'image/jpeg')->orderByDesc('sort_order')->orderByDesc('id')->value('file_path');
        }

        foreach ($resume->sections ?? [] as $key) {
            $visibility = [
                'skills' => 'display_skills',
                'projects' => 'display_projects',
                'certifications' => 'display_certifications',
                'references' => 'display_references',
                'social' => 'display_social',
            ];
            if (isset($visibility[$key]) && ! ($customization[$visibility[$key]] ?? true)) {
                continue;
            }
            if ($templateBlocks && ! in_array($key, $templateBlocks, true)) {
                continue;
            }

            $items = match ($key) {
                'experience' => $profile->experiences()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['experiences'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Experience $experience) => [
                    'title' => $experience->position,
                    'subtitle' => $experience->company,
                    'meta' => $this->dateRange($experience->start_date, $experience->is_current ? null : $experience->end_date, $experience->is_current, $locale),
                    'description' => $this->localizedText($experience->description, $locale),
                    'items' => $this->localizedLines($experience->responsibilities, $locale),
                ])->all(),
                'education' => Education::query()->where('profile_id', $profile->id)->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['educations'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Education $education) => [
                    'title' => $education->degree,
                    'subtitle' => $education->school,
                    'meta' => $this->dateRange($education->start_date, $education->end_date, false, $locale),
                    'description' => $this->localizedText($education->description, $locale),
                    'items' => [],
                ])->all(),
                'skills' => Skill::query()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['skills'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Skill $skill) => [
                    'title' => $this->localizedText($skill->name, $locale),
                    'subtitle' => $skill->level ?? '',
                    'meta' => $skill->percentage !== null ? $skill->percentage.'%' : '',
                    'description' => '',
                    'items' => [],
                ])->all(),
                'projects' => Project::query()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['projects'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Project $project) => [
                    'title' => $this->localizedText($project->title, $locale),
                    'subtitle' => $project->client ?? '',
                    'meta' => $project->project_date ? $this->formatDate($project->project_date, $locale) : '',
                    'description' => $this->localizedText($project->short_description, $locale),
                    'items' => $this->localizedLines($project->technologies, $locale),
                ])->all(),
                'certifications' => Certification::query()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['certifications'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Certification $certification) => [
                    'title' => $this->localizedText($certification->title, $locale),
                    'subtitle' => $certification->issuer ?? '',
                    'meta' => $certification->issue_date ? $this->formatDate($certification->issue_date, $locale) : '',
                    'description' => $this->localizedText($certification->description, $locale),
                    'items' => [],
                ])->all(),
                'languages' => Language::query()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['languages'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Language $language) => [
                    'title' => $this->localizedText($language->name, $locale),
                    'subtitle' => $language->level ?? '',
                    'meta' => '',
                    'description' => '',
                    'items' => [],
                ])->all(),
                'services' => Service::query()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['services'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Service $service) => [
                    'title' => $this->localizedText($service->title, $locale),
                    'subtitle' => '',
                    'meta' => '',
                    'description' => $this->localizedText($service->description, $locale),
                    'items' => [],
                ])->all(),
                'technologies' => Technology::query()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['technologies'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (Technology $technology) => [
                        'title' => $this->localizedText($technology->name, $locale),
                        'subtitle' => $technology->category ?? '',
                        'meta' => '',
                        'description' => '',
                        'items' => [],
                    ])->all(),
                'social' => $profile->socialLinks()->where('is_active', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['social_links'] ?? []))
                    ->orderBy('sort_order')->get()->map(fn (SocialLink $link) => [
                        'title' => $link->label ?: $link->platform,
                        'subtitle' => $link->url,
                        'meta' => '',
                        'description' => '',
                        'items' => [],
                    ])->all(),
                'references' => Schema::hasTable('professional_references')
                    ? $profile->professionalReferences()->where('published', true)
                        ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['references'] ?? []))
                        ->orderBy('sort_order')->get()->map(fn (ProfessionalReference $reference) => [
                            'title' => $reference->name,
                            'subtitle' => implode(' · ', array_filter([$reference->role, $reference->company])),
                            'meta' => implode(' · ', array_filter([$reference->email, $reference->phone])),
                            'description' => '',
                            'items' => [],
                        ])->all()
                    : [],
                'achievements' => $profile->experiences()->where('published', true)
                    ->when($selection !== null, fn ($query) => $query->whereIn('id', $selection['experiences'] ?? []))
                    ->orderBy('sort_order')->get()->flatMap(fn (Experience $experience) => collect($this->localizedLines($experience->achievements, $locale))
                        ->map(fn (string $achievement) => [
                            'title' => $achievement,
                            'subtitle' => $experience->company,
                            'meta' => '',
                            'description' => '',
                            'items' => [],
                        ]))->all(),
                default => [],
            };

            $items = $this->limitItems($items, $key, $resume->length ?? 'two_pages');

            $sections[] = [
                'key' => $key,
                'title' => $this->sectionTitle($key, $locale),
                'items' => $items,
            ];
        }

        return [
            'profile' => [
                'name' => trim(($profile->first_name ?? '').' '.($profile->last_name ?? '')),
                'title' => $resume->target_role ?: ($profile->title ?? ''),
                'summary' => $resume->summary_override ?: ($profile->summary ?? ''),
                'email' => $profile->email ?? '',
                'phone' => $profile->phone ?? '',
                'location' => $profile->location ?? '',
                'website' => $profile->website ?? '',
                'linkedin' => $profile->linkedin ?? '',
                'github' => $profile->github ?? '',
                'avatar_path' => $avatarPath,
            ],
            'sections' => $sections,
        ];
    }

    private function limitItems(array $items, string $section, string $length): array
    {
        if ($length === 'full') {
            return $items;
        }

        $limits = $length === 'one_page'
            ? ['experience' => 2, 'education' => 1, 'skills' => 8, 'projects' => 2, 'certifications' => 2, 'languages' => 4, 'technologies' => 8, 'services' => 2, 'social' => 4, 'references' => 2, 'achievements' => 3]
            : ['experience' => 5, 'education' => 4, 'skills' => 16, 'projects' => 5, 'certifications' => 6, 'languages' => 8, 'technologies' => 16, 'services' => 5, 'social' => 8, 'references' => 5, 'achievements' => 10];

        return array_slice($items, 0, $limits[$section] ?? count($items));
    }

    private function localizedText(mixed $value, string $locale): string
    {
        if (is_array($value)) {
            $translated = $value[$locale] ?? $value['fr'] ?? reset($value) ?: '';

            return is_array($translated) ? implode(' ', array_map('strval', $translated)) : (string) $translated;
        }

        return (string) ($value ?? '');
    }

    private function localizedLines(mixed $value, string $locale): array
    {
        if (! is_array($value)) {
            return $value ? [(string) $value] : [];
        }

        $lines = $value[$locale] ?? $value['fr'] ?? $value;

        return array_values(array_filter(array_map('strval', is_array($lines) ? $lines : [$lines])));
    }

    private function dateRange(mixed $start, mixed $end, bool $current, string $locale): string
    {
        $startDate = $start ? $this->formatDate($start, $locale) : '';
        $endDate = $current
            ? ($locale === 'fr' ? 'Aujourd’hui' : 'Present')
            : ($end ? $this->formatDate($end, $locale) : '');

        return trim($startDate.($startDate && $endDate ? ' - ' : '').$endDate);
    }

    private function formatDate(mixed $date, string $locale): string
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->locale($locale)->translatedFormat('M Y');
    }

    private function sectionTitle(string $key, string $locale): string
    {
        $titles = [
            'experience' => ['fr' => 'Expériences professionnelles', 'en' => 'Professional experience'],
            'education' => ['fr' => 'Formation', 'en' => 'Education'],
            'skills' => ['fr' => 'Compétences', 'en' => 'Skills'],
            'projects' => ['fr' => 'Projets', 'en' => 'Projects'],
            'certifications' => ['fr' => 'Certifications', 'en' => 'Certifications'],
            'languages' => ['fr' => 'Langues', 'en' => 'Languages'],
            'services' => ['fr' => 'Services', 'en' => 'Services'],
            'technologies' => ['fr' => 'Technologies', 'en' => 'Technologies'],
            'references' => ['fr' => 'Références', 'en' => 'References'],
            'social' => ['fr' => 'Réseaux sociaux', 'en' => 'Social links'],
            'achievements' => ['fr' => 'Réalisations', 'en' => 'Achievements'],
        ];

        return $titles[$key][$locale] ?? $titles[$key]['fr'] ?? $key;
    }
}
