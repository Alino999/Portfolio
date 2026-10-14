<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\Education;
use App\Models\SocialLink;
use App\Models\Profile;
use App\Models\Achievement;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Language;
use App\Models\ProfessionalReference;
use App\Models\Project;
use App\Models\Section;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_skills_management(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.skills.index'))
            ->assertOk();
    }

    public function test_admin_can_reset_portfolio_data_without_deleting_the_account(): void
    {
        Schema::create('pma__bookmark', function ($table): void {
            $table->id();
            $table->string('label');
        });
        DB::table('pma__bookmark')->insert(['label' => 'phpMyAdmin bookmark']);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        SiteSetting::query()->create(['key' => 'site_name', 'value' => 'Mon portfolio']);

        $this->actingAs($user)
            ->post(route('admin.settings.reset'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseCount('site_settings', 0);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('pma__bookmark', ['label' => 'phpMyAdmin bookmark']);
    }

    public function test_every_portfolio_content_tab_offers_import_and_export(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        foreach ([
            'educations',
            'certifications',
            'languages',
            'technologies',
            'professional-references',
            'social-links',
            'achievements',
        ] as $type) {
            $this->actingAs($user)
                ->get(route('admin.portfolio-content.index', $type))
                ->assertOk()
                ->assertSee(route('admin.portfolio-content.export', $type), false)
                ->assertSee(route('admin.portfolio-content.import', $type), false);
        }
    }

    public function test_admin_can_import_and_export_the_current_content_tab_as_json(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $user->id]);
        $otherProfile = Profile::query()->create(['user_id' => User::factory()->create()->id]);
        Education::query()->create([
            'profile_id' => $profile->id,
            'school' => 'Université Aube Nouvelle',
            'degree' => 'Master',
            'field' => 'Ancien domaine',
        ]);
        Education::query()->create([
            'profile_id' => $otherProfile->id,
            'school' => 'Autre profil',
            'degree' => 'Licence',
        ]);

        $export = $this->actingAs($user)
            ->get(route('admin.portfolio-content.export', 'educations'))
            ->assertOk()
            ->assertHeader('content-type', 'application/json');
        $payload = json_decode($export->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('educations', $payload['type']);
        $this->assertCount(1, $payload['entries']);
        $this->assertArrayNotHasKey('profile_id', $payload['entries'][0]);

        $payload['entries'] = [
            array_replace($payload['entries'][0], ['description' => ['fr' => 'Formation importée', 'en' => 'Imported education']]),
            [
                'school' => 'FORCE-N',
                'degree' => 'Analyste en cybersécurité',
                'field' => 'Cybersécurité',
                'start_date' => '2025-05-01',
                'end_date' => '2025-05-01',
                'image_path' => '../../outside.png',
                'sort_order' => 2,
                'published' => true,
            ],
        ];

        $this->post(route('admin.portfolio-content.import', 'educations'), [
            'import_file' => UploadedFile::fake()->createWithContent('educations.json', json_encode($payload)),
        ])->assertRedirect(route('admin.portfolio-content.index', 'educations'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('educations', 3);
        $this->assertDatabaseHas('educations', [
            'profile_id' => $profile->id,
            'school' => 'Université Aube Nouvelle',
            'degree' => 'Master',
        ]);
        $this->assertDatabaseHas('educations', ['profile_id' => $otherProfile->id, 'school' => 'Autre profil']);
        $this->assertNull(Education::query()->where('profile_id', $profile->id)->where('school', 'FORCE-N')->value('image_path'));
        $this->assertSame(['fr' => 'Formation importée', 'en' => 'Imported education'], Education::query()
            ->where('profile_id', $profile->id)
            ->where('school', 'Université Aube Nouvelle')
            ->value('description'));

        $this->post(route('admin.portfolio-content.import', 'certifications'), [
            'import_file' => UploadedFile::fake()->createWithContent('wrong-tab.json', json_encode($payload)),
        ])->assertSessionHasErrors('import_file');
    }

    public function test_admin_can_download_a_portfolio_backup(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        SiteSetting::query()->create(['key' => 'site_name', 'value' => 'Mon portfolio']);
        Project::query()->create([
            'title' => ['fr' => 'Projet de sauvegarde', 'en' => 'Backup project'],
            'slug' => 'projet-de-sauvegarde',
            'published' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('name="groups[]" value="site" checked', false)
            ->assertSee('name="groups[]" value="articles" checked', false)
            ->assertSee('name="groups[]" value="resumes" checked', false);

        $this->actingAs($user)
            ->get(route('admin.settings.backup'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json; charset=UTF-8');
    }

    public function test_admin_can_choose_backup_groups(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        SiteSetting::query()->create(['key' => 'site_name', 'value' => 'Mon portfolio']);
        Project::query()->create([
            'title' => ['fr' => 'Projet d’un autre groupe', 'en' => 'Project in another group'],
            'slug' => 'projet-autre-groupe',
            'published' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.settings.backup', ['groups' => ['articles']]));
        $response->assertOk()->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $payload = json_decode($response->streamedContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(['articles'], $payload['selected_groups']);
        $this->assertArrayHasKey('articles', $payload['tables']);
        $this->assertArrayNotHasKey('site_settings', $payload['tables']);
        $this->assertArrayNotHasKey('projects', $payload['tables']);
    }

    public function test_admin_can_restore_a_portfolio_backup(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        SiteSetting::query()->create(['key' => 'site_name', 'value' => 'Ancien nom']);

        $backup = [
            'generated_at' => now()->toJson(),
            'tables' => [
                'site_settings' => [
                    ['key' => 'site_name', 'value' => 'Nouveau nom restauré'],
                ],
                'projects' => [
                    [
                        'title' => ['fr' => 'Projet restauré', 'en' => 'Restored project'],
                        'slug' => 'projet-restaure',
                        'published' => 1,
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->from(route('admin.settings'))
            ->post(route('admin.settings.restore'), [
                'backup_file' => UploadedFile::fake()->createWithContent(
                    'portfolio-backup.json',
                    json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ),
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'site_name', 'value' => 'Nouveau nom restauré']);
        $this->assertDatabaseHas('projects', ['slug' => 'projet-restaure']);
    }

    public function test_admin_can_upload_a_brand_logo_and_save_its_palette(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $logo = UploadedFile::fake()->createWithContent(
            'brand.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
        );

        $this->actingAs($user)
            ->from(route('admin.settings'))
            ->post(route('admin.settings.save'), [
                'site_name' => 'SecureTech & Maintenance',
                'primary_color' => '#125c55',
                'accent_color' => '#d8785f',
                'logo' => $logo,
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors();

        $logoPath = SiteSetting::query()->where('key', 'logo_path')->value('value');
        $this->assertDatabaseHas('site_settings', ['key' => 'primary_color', 'value' => '#125c55']);
        $this->assertDatabaseHas('site_settings', ['key' => 'accent_color', 'value' => '#d8785f']);
        Storage::disk('public')->assertExists($logoPath);

        $logoUrl = asset('storage/'.$logoPath);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($logoUrl)
            ->assertSee('SecureTech &amp; Maintenance', false)
            ->assertSee('--brand-accent: #d8785f', false);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($logoUrl)
            ->assertSee('--brand-primary: #125c55', false);
    }

    public function test_admin_can_add_an_education_with_an_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $image = UploadedFile::fake()->createWithContent(
            'formation.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
        );

        $this->actingAs($user)
            ->get(route('admin.portfolio-content.index', 'educations'))
            ->assertOk()
            ->assertSee('Formations');

        $this->post(route('admin.portfolio-content.store', 'educations'), [
            'school' => 'Université de Ouagadougou',
            'degree' => 'Master cybersécurité',
            'field' => 'Sécurité informatique',
            'image' => $image,
            'published' => true,
        ])->assertRedirect(route('admin.portfolio-content.index', 'educations'))
            ->assertSessionHasNoErrors();

        $education = Education::query()->where('school', 'Université de Ouagadougou')->firstOrFail();
        $this->assertNotNull($education->image_path);
        Storage::disk('public')->assertExists($education->image_path);
    }

    public function test_admin_can_add_an_active_social_link_in_the_content_manager(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('admin.portfolio-content.store', 'social-links'), [
                'platform' => 'LinkedIn',
                'url' => 'https://linkedin.com/in/example',
                'published' => true,
            ])
            ->assertRedirect(route('admin.portfolio-content.index', 'social-links'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('social_links', [
            'platform' => 'LinkedIn',
            'url' => 'https://linkedin.com/in/example',
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_add_all_remaining_content_types_with_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $contentTypes = [
            'certifications' => ['table' => 'certifications', 'data' => ['title_fr' => 'Certification cloud']],
            'languages' => ['table' => 'languages', 'data' => ['name_fr' => 'Espagnol', 'level' => 'Intermédiaire']],
            'technologies' => ['table' => 'technologies', 'data' => ['name_fr' => 'Docker', 'category' => 'DevOps']],
            'professional-references' => ['table' => 'professional_references', 'data' => ['name' => 'Référence Test', 'role' => 'Directeur']],
            'achievements' => ['table' => 'achievements', 'data' => ['title_fr' => 'Prix innovation']],
        ];

        foreach ($contentTypes as $type => $contentType) {
            $this->actingAs($user)
                ->get(route('admin.portfolio-content.index', $type))
                ->assertOk();

            $response = $this->actingAs($user)
                ->post(route('admin.portfolio-content.store', $type), $contentType['data'] + [
                    'image' => UploadedFile::fake()->createWithContent(
                        $type.'.png',
                        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
                    ),
                    'published' => true,
                ]);

            $response->assertRedirect(route('admin.portfolio-content.index', $type))
                ->assertSessionHasNoErrors();

            $entry = DB::table($contentType['table'])->first();
            $this->assertNotNull($entry?->image_path, "Expected an uploaded image for {$type}.");
            Storage::disk('public')->assertExists($entry->image_path);
            $this->get(route('admin.portfolio-content.edit', ['type' => $type, 'entry' => $entry->id]))
                ->assertOk();
        }
    }

    public function test_public_portfolio_renders_all_content_sections_as_image_carousels(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('portfolio/shared.png', 'image');
        $user = User::factory()->create();
        $profile = Profile::query()->create(['user_id' => $user->id]);

        foreach (['experience', 'projects', 'services', 'certifications', 'skills'] as $key) {
            Section::query()->create(['key' => $key, 'title' => ['fr' => $key], 'enabled' => true]);
        }

        Experience::query()->create([
            'profile_id' => $profile->id, 'company' => 'Société Test', 'position' => 'Consultante',
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        Education::query()->create([
            'profile_id' => $profile->id, 'school' => 'École Test', 'degree' => 'Diplôme Test',
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        Skill::query()->create([
            'name' => ['fr' => 'Compétence Test', 'en' => 'Test Skill'],
            'image_path' => 'portfolio/shared.png', 'percentage' => 80, 'published' => true,
        ]);
        Project::query()->create([
            'title' => ['fr' => 'Projet Test', 'en' => 'Test Project'], 'slug' => 'projet-test',
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        Service::query()->create([
            'title' => ['fr' => 'Service Test', 'en' => 'Test Service'],
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        Certification::query()->create([
            'title' => ['fr' => 'Certification Test', 'en' => 'Test Certification'],
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        Language::query()->create([
            'name' => ['fr' => 'Langue Test', 'en' => 'Test Language'],
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        Technology::query()->create([
            'name' => ['fr' => 'Technologie Test', 'en' => 'Test Technology'],
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        Achievement::query()->create([
            'profile_id' => $profile->id, 'title' => ['fr' => 'Réalisation Test', 'en' => 'Test Achievement'],
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        ProfessionalReference::query()->create([
            'profile_id' => $profile->id, 'name' => 'Référence Test',
            'image_path' => 'portfolio/shared.png', 'published' => true,
        ]);
        SocialLink::query()->create([
            'profile_id' => $profile->id, 'platform' => 'LinkedIn', 'url' => 'https://linkedin.com/in/example',
            'image_path' => 'portfolio/shared.png', 'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'fr'])->get(route('home'))->assertOk();
        foreach ([
            'Compétence Test', 'Société Test', 'École Test', 'Projet Test', 'Service Test',
            'Certification Test', 'Langue Test', 'Technologie Test', 'Réalisation Test',
            'Référence Test', 'LinkedIn',
        ] as $label) {
            $response->assertSee($label);
        }
        $response->assertSee('data-portfolio-carousel', false)
            ->assertSee('portfolio-entry-image', false);
    }

    public function test_admin_can_remove_an_existing_brand_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        Storage::disk('public')->put('branding/old-logo.png', 'old logo');
        SiteSetting::query()->create(['key' => 'logo_path', 'value' => 'branding/old-logo.png']);

        $this->actingAs($user)
            ->from(route('admin.settings'))
            ->post(route('admin.settings.save'), ['remove_logo' => true])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'logo_path', 'value' => null]);
        Storage::disk('public')->assertMissing('branding/old-logo.png');
    }

    public function test_admin_can_create_a_skill(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.skills.store'), [
                'name_fr' => 'Laravel',
                'name_en' => 'Laravel',
                'level' => 'Expert',
                'level_en' => 'Expert',
                'percentage' => 90,
                'years_experience' => 5,
                'description_fr' => 'Développement web backend',
                'description_en' => 'Backend web development',
                'image' => UploadedFile::fake()->createWithContent(
                    'laravel.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
                ),
                'sort_order' => 1,
                'published' => true,
            ]);

        $response->assertRedirect(route('admin.skills.index'));
        $skill = Skill::query()->where('name->fr', 'Laravel')->firstOrFail();
        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'level' => 'Expert',
            'level_en' => 'Expert',
            'published' => 1,
            'image_path' => $skill->image_path,
        ]);
        Storage::disk('public')->assertExists($skill->image_path);
    }

    public function test_admin_can_remove_a_missing_skill_image_and_delete_the_skill(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $skill = Skill::query()->create([
            'name' => ['fr' => 'Sans photo', 'en' => 'No photo'],
        ]);

        $this->actingAs($user)
            ->put(route('admin.skills.update', $skill), [
                'name_fr' => 'Sans photo',
                'name_en' => 'No photo',
                'remove_image' => true,
            ])
            ->assertRedirect(route('admin.skills.index'));

        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'image_path' => null]);

        $this->delete(route('admin.skills.destroy', $skill))
            ->assertRedirect(route('admin.skills.index'));

        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_admin_can_save_english_profile_translations_and_the_site_displays_them(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.profile.update'), [
                'title' => 'Consultant informatique',
                'title_en' => 'IT consultant',
                'subtitle' => 'Conseil en systèmes informatiques',
                'subtitle_en' => 'Information technology consulting',
                'summary' => 'Résumé français du profil.',
                'summary_en' => 'English profile summary.',
                'bio' => 'Biographie française complète.',
                'bio_en' => 'Full English biography.',
            ])
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'subtitle_en' => 'Information technology consulting',
            'summary_en' => 'English profile summary.',
            'bio_en' => 'Full English biography.',
        ]);

        $this->withSession(['locale' => 'en'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Information technology consulting')
            ->assertSee('English profile summary.');
    }
}
