<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\ProfessionalReference;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Models\Achievement;
use App\Services\CvImporter;
use App\Services\CvTranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProfileController extends Controller
{
    public function edit()
    {
        $profile = Profile::query()->firstOrCreate(['user_id' => auth()->id()], [
            'title' => 'Consultant informatique & cybersécurité',
            'title_en' => 'IT consultant & cybersecurity',
            'subtitle' => 'Sage X3 / ERP / SI',
            'subtitle_en' => 'Sage X3 / ERP / IT systems',
            'summary' => 'Portfolio de démonstration',
            'summary_en' => 'Demonstration portfolio',
            'email' => 'contact@example.com',
        ]);

        return view('admin.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $profile = Profile::query()->firstOrCreate(['user_id' => auth()->id()]);

        $data = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'subtitle_en' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'summary_en' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'bio_en' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'availability' => ['nullable', 'string', 'max:100'],
            'professional_status' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0'],
            'website' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
        ]);

        $profile->fill(array_merge($data, ['user_id' => auth()->id()]));
        $profile->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Profil mis à jour.');
    }

    public function import(Request $request, CvImporter $cvImporter, CvTranslationService $cvTranslationService)
    {
        $request->validate([
            'cv_text' => ['nullable', 'string'],
            'cv_file' => ['nullable', 'file', 'mimes:txt,md,doc,docx,pdf'],
        ]);

        $profile = Profile::query()->firstOrCreate(['user_id' => auth()->id()]);

        $rawContent = trim((string) ($request->input('cv_text') ?? ''));
        $uploadedFile = null;

        if ($request->hasFile('cv_file')) {
            $uploadedFile = $request->file('cv_file');
            $rawContent = trim($cvImporter->extractFile($uploadedFile));
        }

        if ($rawContent === '') {
            return back()->with('error', 'Aucun texte de CV fourni.');
        }

        $parsed = $cvImporter->parse($rawContent);
        try {
            $parsed = $cvTranslationService->translate($parsed);
        } catch (RuntimeException $exception) {
            report($exception);

            return back()->withInput()->with('error', $exception->getMessage());
        }

        if ($uploadedFile) {
            $profile->cv_path = $uploadedFile->store('cv-imports', 'public');
        }

        if (! empty($parsed['first_name']) || ! empty($parsed['last_name'])) {
            $profile->first_name = $parsed['first_name'] ?? $profile->first_name;
            $profile->last_name = $parsed['last_name'] ?? $profile->last_name;
        }

        $profile->title = $parsed['title'] ?? $profile->title;
        $profile->title_en = $parsed['title_en'] ?? $profile->title_en;
        $profile->email = $parsed['email'] ?? $profile->email;
        $profile->phone = $parsed['phone'] ?? $profile->phone;
        $profile->website = $parsed['website'] ?? $profile->website;
        $profile->location = $parsed['location'] ?? $profile->location;
        $profile->summary = $parsed['summary'] ?? $profile->summary;
        $profile->summary_en = $parsed['summary_en'] ?? $profile->summary_en;
        foreach ($parsed['social_links'] ?? [] as $socialLink) {
            if (($socialLink['platform'] ?? null) === 'LinkedIn') $profile->linkedin = $socialLink['url'];
            if (($socialLink['platform'] ?? null) === 'GitHub') $profile->github = $socialLink['url'];
        }
        $profile->save();

        $updatedExperienceIds = [];
        foreach (($parsed['experiences'] ?? []) as $item) {
            $companyExperiences = Experience::query()
                ->where('profile_id', $profile->id)
                ->where('company', $item['company'])
                ->whereNotIn('id', $updatedExperienceIds)
                ->get();
            $experience = $companyExperiences->firstWhere('position', $item['position']);
            if (! $experience && $companyExperiences->count() === 1) {
                $experience = $companyExperiences->first();
            }

            $description = $item['description'] ?? null;
            $experienceData = [
                'company' => $item['company'],
                'company_en' => $item['company'],
                'position' => $item['position'],
                'position_en' => $item['position_en'] ?? $item['position'],
                'location' => $item['location'] ?? null,
                'start_date' => $item['start_date'] ?? null,
                'end_date' => $item['end_date'] ?? null,
                'is_current' => $item['is_current'] ?? false,
                'description' => $description === null ? null : [
                    'fr' => $description,
                    'en' => $item['description_en'] ?? $description,
                ],
                'responsibilities' => [
                    'fr' => $item['responsibilities'] ?? [],
                    'en' => $item['responsibilities_en'] ?? ($item['responsibilities'] ?? []),
                ],
                'achievements' => [
                    'fr' => $item['achievements'] ?? [],
                    'en' => $item['achievements_en'] ?? ($item['achievements'] ?? []),
                ],
                'published' => true,
                'sort_order' => 0,
            ];
            if ($experience) {
                $experience->fill($experienceData)->save();
                $updatedExperienceIds[] = $experience->id;
            } else {
                Experience::query()->create(array_merge([
                    'profile_id' => $profile->id,
                ], $experienceData));
            }
        }

        foreach (($parsed['educations'] ?? []) as $item) {
            $school = trim((string) ($item['school'] ?? '')) ?: 'École / Université';
            $degree = trim((string) ($item['degree'] ?? '')) ?: 'Formation';

            Education::query()->updateOrCreate([
                'profile_id' => $profile->id,
                'school' => $school,
                'degree' => $degree,
            ], [
                'degree_en' => $item['degree_en'] ?? $degree,
                'field_en' => $item['field_en'] ?? ($item['field'] ?? null),
                'field' => $item['field'] ?? null,
                'start_date' => $item['start_date'] ?? null,
                'end_date' => $item['end_date'] ?? null,
                'description' => isset($item['description']) ? [
                    'fr' => $item['description'],
                    'en' => $item['description_en'] ?? $item['description'],
                ] : null,
                'published' => true,
                'sort_order' => 0,
            ]);
        }

        foreach (($parsed['skills'] ?? []) as $index => $skillName) {
            $skillName = trim((string) $skillName);
            if ($skillName === '') {
                continue;
            }

            $skill = Skill::query()->where('name->fr', $skillName)->first();
            if ($skill) {
                $skill->update(['name' => ['fr' => $skillName, 'en' => $parsed['skills_en'][$index] ?? $skillName]]);
            } else {
                Skill::query()->create([
                    'name' => ['fr' => $skillName, 'en' => $parsed['skills_en'][$index] ?? $skillName],
                    'published' => true,
                ]);
            }
        }

        foreach (($parsed['certifications'] ?? []) as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') continue;

            Certification::query()->updateOrCreate([
                'title->fr' => $title,
                'issuer' => $item['issuer'] ?? null,
            ], [
                'title' => ['fr' => $title, 'en' => $item['title_en'] ?? $title],
                'issuer_en' => $item['issuer'] ?? null,
                'issue_date' => $item['issue_date'] ?? null,
                'description' => isset($item['description']) ? [
                    'fr' => $item['description'],
                    'en' => $item['description_en'] ?? $item['description'],
                ] : null,
                'published' => true,
                'sort_order' => Certification::query()->max('sort_order') + $index + 1,
            ]);
        }

        foreach (($parsed['languages'] ?? []) as $index => $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') continue;

            $languageQuery = Language::query();
            $item['locale'] ? $languageQuery->where('locale', $item['locale']) : $languageQuery->where('name->fr', $name);
            $languageData = [
                    'name' => ['fr' => $name, 'en' => $item['name_en'] ?? $name],
                    'locale' => $item['locale'] ?? null,
                    'level' => $item['level'] ?? null,
                    'level_en' => $item['level_en'] ?? ($item['level'] ?? null),
                    'sort_order' => Language::query()->max('sort_order') + $index + 1,
                    'published' => true,
                ];
            $existingLanguage = $languageQuery->first();
            if ($existingLanguage) {
                $existingLanguage->update($languageData);
            } else {
                Language::query()->create($languageData);
            }
        }

        foreach (($parsed['technologies'] ?? []) as $index => $name) {
            $name = trim((string) $name);
            if ($name === '' || Technology::query()->where('name->fr', $name)->exists()) continue;

            Technology::query()->create([
                'name' => ['fr' => $name, 'en' => $name],
                'sort_order' => Technology::query()->max('sort_order') + $index + 1,
                'published' => true,
            ]);
        }

        foreach (($parsed['references'] ?? []) as $index => $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') continue;

            ProfessionalReference::query()->updateOrCreate([
                'profile_id' => $profile->id,
                'name' => $name,
            ], [
                'role' => $item['role'] ?? null,
                'role_en' => $item['role_en'] ?? ($item['role'] ?? null),
                'company' => $item['company'] ?? null,
                'phone' => $item['phone'] ?? null,
                'email' => $item['email'] ?? null,
                'sort_order' => $profile->professionalReferences()->max('sort_order') + $index + 1,
                'published' => true,
            ]);
        }

        foreach (($parsed['social_links'] ?? []) as $index => $item) {
            if (empty($item['platform']) || empty($item['url'])) continue;

            SocialLink::query()->firstOrCreate([
                'profile_id' => $profile->id,
                'platform' => $item['platform'],
            ], [
                'label' => $item['label'] ?? $item['platform'],
                'url' => $item['url'],
                'icon' => $item['icon'] ?? null,
                'sort_order' => $profile->socialLinks()->max('sort_order') + $index + 1,
                'is_active' => true,
            ]);
        }

        foreach (($parsed['projects'] ?? []) as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') continue;

            $slug = \Illuminate\Support\Str::slug($title) ?: 'projet-import';
            $baseSlug = $slug;
            $suffix = 2;
            while (Project::query()->where('slug', $slug)->where('title->fr', '!=', $title)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }
            Project::query()->firstOrCreate(['slug' => $slug], [
                'title' => ['fr' => $title, 'en' => $item['title_en'] ?? $title],
                'short_description' => ['fr' => $item['description'] ?? '', 'en' => $item['description_en'] ?? ($item['description'] ?? '')],
                'description' => ['fr' => $item['description'] ?? '', 'en' => $item['description_en'] ?? ($item['description'] ?? '')],
                'project_date' => $item['project_date'] ?? null,
                'status' => 'published',
                'published' => true,
                'sort_order' => Project::query()->max('sort_order') + $index + 1,
            ]);
        }

        foreach (($parsed['achievements'] ?? []) as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') continue;

            Achievement::query()->firstOrCreate([
                'profile_id' => $profile->id,
                'title->fr' => $title,
            ], [
                'title' => ['fr' => $title, 'en' => $item['title_en'] ?? $title],
                'description' => isset($item['description']) ? [
                    'fr' => $item['description'],
                    'en' => $item['description_en'] ?? $item['description'],
                ] : null,
                'achievement_date' => $item['project_date'] ?? null,
                'sort_order' => $profile->achievements()->max('sort_order') + $index + 1,
                'published' => true,
            ]);
        }

        $importedSections = collect([
            'expériences' => count($parsed['experiences'] ?? []),
            'formations' => count($parsed['educations'] ?? []),
            'compétences' => count($parsed['skills'] ?? []),
            'certifications' => count($parsed['certifications'] ?? []),
            'langues' => count($parsed['languages'] ?? []),
            'technologies' => count($parsed['technologies'] ?? []),
            'références' => count($parsed['references'] ?? []),
            'réseaux sociaux' => count($parsed['social_links'] ?? []),
            'projets' => count($parsed['projects'] ?? []),
            'réalisations' => count($parsed['achievements'] ?? []),
        ])->filter(fn ($count) => $count > 0)->keys()->implode(', ');

        return redirect()->route('admin.profile.edit')->with('success', 'CV importé. Rubriques détectées : '.($importedSections ?: 'profil').'. Vérifiez les éléments dans chaque onglet avant publication.');
    }
}
