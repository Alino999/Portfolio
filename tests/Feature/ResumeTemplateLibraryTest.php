<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\ResumeTemplateCategory;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use App\Services\ResumeContentBuilder;
use Database\Seeders\ResumeTemplateSeeder;
use Database\Seeders\AdditionalResumeTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeTemplateLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_photo_block_controls_photo_support(): void
    {
        $withPhoto = new ResumeTemplate(['layout' => 'classic', 'blocks' => ['header', 'photo']]);
        $withoutPhoto = new ResumeTemplate(['layout' => 'classic', 'blocks' => ['header', 'experience']]);
        $legacyPhotoLayout = new ResumeTemplate(['layout' => 'portfolio-sidebar']);

        $this->assertTrue($withPhoto->supportsPhoto());
        $this->assertFalse($withoutPhoto->supportsPhoto());
        $this->assertTrue($legacyPhotoLayout->supportsPhoto());
    }

    public function test_template_library_seeds_eighteen_distinct_templates_and_dynamic_categories(): void
    {
        $this->seed(ResumeTemplateSeeder::class);

        $this->assertSame(18, ResumeTemplate::query()->where('is_system', true)->count());
        $this->assertGreaterThanOrEqual(14, ResumeTemplateCategory::query()->count());
        $this->assertTrue(ResumeTemplate::query()->where('ats_friendly', true)->exists());
        $this->assertSame(18, ResumeTemplate::query()->where('is_system', true)->distinct('layout')->count('layout'));
        $this->assertSame('editorial-nordic', ResumeTemplate::query()->where('slug', 'editorial-nordic')->value('layout'));
        $this->assertSame('portfolio-showcase', ResumeTemplate::query()->where('slug', 'portfolio-showcase')->value('layout'));
        $this->assertSame('bold-header', ResumeTemplate::query()->where('slug', 'bold-header')->value('layout'));
        $this->assertSame('graduate-focus', ResumeTemplate::query()->where('slug', 'graduate-focus')->value('layout'));
        $this->assertSame('portfolio-sidebar', ResumeTemplate::query()->where('slug', 'portfolio-sidebar')->value('layout'));
        $this->assertSame('warm-academic', ResumeTemplate::query()->where('slug', 'warm-academic')->value('layout'));
        $this->assertSame('portfolio-sidebar', ResumeTemplate::query()->where('slug', 'portfolio-sidebar')->value('layout'));
        $this->assertSame('warm-academic', ResumeTemplate::query()->where('slug', 'warm-academic')->value('layout'));

        $template = ResumeTemplate::query()->where('slug', 'ats-clean')->firstOrFail();
        $this->assertTrue($template->ats_friendly);
        $this->assertContains('fr', $template->languages);
        $this->assertContains('pdf', $template->available_formats);
        $this->assertNotEmpty($template->blocks);
        $this->assertSame('ats', $template->category->slug);
    }

    public function test_template_seeder_is_idempotent_and_preserves_existing_template_names(): void
    {
        ResumeTemplate::query()->create([
            'name' => 'Mon modèle personnalisé',
            'slug' => 'modern-executive',
            'layout' => 'custom-executive',
        ]);

        $this->seed(ResumeTemplateSeeder::class);
        $this->seed(ResumeTemplateSeeder::class);

        $this->assertSame(1, ResumeTemplate::query()->where('slug', 'modern-executive')->count());
        $this->assertSame('Mon modèle personnalisé', ResumeTemplate::query()->where('slug', 'modern-executive')->value('name'));
        $this->assertSame(18, ResumeTemplate::query()->where('is_system', true)->count());
    }

    public function test_additional_template_seeder_is_additive_and_idempotent(): void
    {
        $existing = ResumeTemplate::query()->create([
            'name' => 'Personal template',
            'slug' => 'personal-template',
            'layout' => 'custom-layout',
            'is_system' => false,
        ]);

        $this->seed(AdditionalResumeTemplateSeeder::class);
        $this->seed(AdditionalResumeTemplateSeeder::class);

        $this->assertSame(6, ResumeTemplate::query()->whereIn('slug', [
            'editorial-nordic', 'portfolio-showcase', 'bold-header', 'graduate-focus', 'portfolio-sidebar', 'warm-academic',
        ])->count());
        $this->assertDatabaseHas('resume_templates', [
            'id' => $existing->id,
            'name' => 'Personal template',
            'layout' => 'custom-layout',
            'is_system' => false,
        ]);
    }

    public function test_resume_persists_exact_content_selection_and_customization(): void
    {
        $this->seed(ResumeTemplateSeeder::class);
        $user = \App\Models\User::factory()->create();
        $profile = \App\Models\Profile::query()->create(['user_id' => $user->id]);
        $template = ResumeTemplate::query()->where('slug', 'tech')->firstOrFail();
        $selection = [
            'experiences' => [12, 15],
            'skills' => [3, 8],
            'projects' => [6],
            'certifications' => [],
            'educations' => [2],
            'languages' => [1],
        ];
        $customization = [
            'primary_color' => '#17324d',
            'secondary_color' => '#0f766e',
            'font' => 'inter',
            'font_size' => 10,
            'include_photo' => false,
            'target_market' => 'international',
        ];

        $resume = Resume::query()->create([
            'profile_id' => $profile->id,
            'title' => 'CV Consultant IT',
            'slug' => 'cv-consultant-it',
            'resume_template_id' => $template->id,
            'content_selection' => $selection,
            'customization' => $customization,
            'target_role' => 'Consultant IT',
            'summary_override' => 'Résumé spécialisé.',
        ]);

        $this->assertSame($selection, $resume->fresh()->content_selection);
        $this->assertSame($customization, $resume->fresh()->customization);
        $this->assertSame('Consultant IT', $resume->target_role);
        $this->assertSame('Résumé spécialisé.', $resume->summary_override);
    }

    public function test_resume_photo_setting_follows_template_and_controls_builder_output(): void
    {
        $this->seed(ResumeTemplateSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'avatar_path' => 'photos/profile.jpg',
        ]);
        $withPhoto = ResumeTemplate::query()->where('slug', 'classic-professional')->firstOrFail();
        $withPhoto->update(['blocks' => [...$withPhoto->blocks, 'photo']]);
        $withoutPhoto = ResumeTemplate::query()->where('slug', 'ats-clean')->firstOrFail();
        $withoutPhoto->update(['blocks' => array_values(array_diff($withoutPhoto->blocks, ['photo']))]);

        $this->actingAs($user)
            ->get(route('admin.resumes.index', ['template' => $withPhoto->id]))
            ->assertOk()
            ->assertSee('id="new-resume-include_photo" checked', false);
        $withoutPhotoResponse = $this->get(route('admin.resumes.index', ['template' => $withoutPhoto->id]));
        $withoutPhotoResponse->assertOk();
        $this->assertMatchesRegularExpression('/id="new-resume-include_photo"[^>]*disabled/', $withoutPhotoResponse->getContent());

        foreach ([[$withPhoto, 'CV avec photo'], [$withoutPhoto, 'CV sans photo']] as [$template, $title]) {
            $this->post(route('admin.resumes.store'), [
                'title' => $title,
                'locale' => 'fr',
                'resume_template_id' => $template->id,
                'length' => 'two_pages',
                'format' => 'pdf',
                'sections' => [],
            ])->assertRedirect(route('admin.resumes.index'));
        }

        $photoResume = Resume::query()->where('title', 'CV avec photo')->with(['profile', 'template'])->firstOrFail();
        $noPhotoResume = Resume::query()->where('title', 'CV sans photo')->with(['profile', 'template'])->firstOrFail();
        $this->assertTrue($photoResume->customization['include_photo']);
        $this->assertFalse($noPhotoResume->customization['include_photo']);
        $this->assertSame('photos/profile.jpg', app(ResumeContentBuilder::class)->build($photoResume)['profile']['avatar_path']);
        $this->assertNull(app(ResumeContentBuilder::class)->build($noPhotoResume)['profile']['avatar_path']);
    }

    public function test_resume_builder_includes_only_selected_experiences_and_skills(): void
    {
        $this->seed(ResumeTemplateSeeder::class);
        $user = User::factory()->create();
        $profile = Profile::query()->create(['user_id' => $user->id]);
        $selectedExperience = Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Entreprise retenue',
            'position' => 'Consultante Sage X3',
            'published' => true,
        ]);
        Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Entreprise écartée',
            'position' => 'Support informatique',
            'published' => true,
        ]);
        $selectedSkill = Skill::query()->create(['name' => ['fr' => 'Cybersécurité', 'en' => 'Cybersecurity'], 'published' => true]);
        Skill::query()->create(['name' => ['fr' => 'Compétence écartée', 'en' => 'Unselected skill'], 'published' => true]);
        $resume = Resume::query()->create([
            'profile_id' => $profile->id,
            'title' => 'CV ciblé',
            'slug' => 'cv-cible',
            'resume_template_id' => ResumeTemplate::query()->where('slug', 'consultant')->value('id'),
            'locale' => 'fr',
            'sections' => ['experience', 'skills'],
            'content_selection' => [
                'experiences' => [$selectedExperience->id],
                'skills' => [$selectedSkill->id],
                'educations' => [],
                'projects' => [],
                'certifications' => [],
                'languages' => [],
            ],
        ]);

        $content = app(ResumeContentBuilder::class)->build($resume->load('profile'));
        $experienceTitles = collect($content['sections'])->firstWhere('key', 'experience')['items'];
        $skillTitles = collect($content['sections'])->firstWhere('key', 'skills')['items'];

        $this->assertSame(['Consultante Sage X3'], array_column($experienceTitles, 'title'));
        $this->assertSame(['Cybersécurité'], array_column($skillTitles, 'title'));
    }
}
