<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\Media;
use App\Models\Profile;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminResumeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_screen_provides_a_default_template_and_sections(): void
    {
        $this->seed(\Database\Seeders\ResumeTemplateSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        Profile::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Lino',
            'last_name' => 'Kaboré',
        ]);

        $this->actingAs($user)
            ->get(route('admin.resumes.index'))
            ->assertOk()
            ->assertSee('Classic Professional')
            ->assertSee('ATS Clean')
            ->assertSee('Executive Premium')
            ->assertSee('cv-template-choice')
            ->assertSee('Expériences');

        $this->assertDatabaseCount('resume_templates', 18);

        $selectedTemplate = ResumeTemplate::query()->where('slug', 'cybersecurity')->firstOrFail();
        $this->actingAs($user)
            ->get(route('admin.resumes.index', ['template' => $selectedTemplate->id]))
            ->assertOk()
            ->assertSee('value="'.$selectedTemplate->id.'" data-formats="pdf,docx"', false)
            ->assertSee('data-primary-color="#162B3D" data-secondary-color="#2A9D8F" checked', false);
    }

    public function test_default_resume_preview_uses_brand_identity_name_and_email(): void
    {
        $this->seed(\Database\Seeders\ResumeTemplateSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::query()->firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user->assignRole($role);
        $template = ResumeTemplate::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.resume-templates.preview', ['resumeTemplate' => $template->id]))
            ->assertOk()
            ->assertSee('Alain Guigma')
            ->assertSee('alain@example.com')
            ->assertDontSee('Amina Traoré')
            ->assertDontSee('amina@example.com');
    }

    public function test_admin_can_create_and_download_pdf_and_docx_resumes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Lino',
            'last_name' => 'Kaboré',
            'title' => 'Consultant R&D & cybersécurité',
            'email' => 'lino@example.test',
        ]);
        Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Entreprise de test',
            'position' => 'Consultant ERP',
            'published' => true,
        ]);
        Skill::query()->create([
            'name' => ['fr' => 'Laravel', 'en' => 'Laravel'],
            'published' => true,
        ]);
        $template = ResumeTemplate::query()->create([
            'name' => 'Créatif',
            'slug' => 'creative',
            'layout' => 'creative',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user)->post(route('admin.resumes.store'), [
            'title' => 'CV professionnel',
            'locale' => 'fr',
            'resume_template_id' => $template->id,
            'length' => 'two_pages',
            'format' => 'pdf',
            'sections' => ['experience', 'skills'],
        ]);

        $response->assertRedirect(route('admin.resumes.index'));
        $resume = Resume::query()->where('profile_id', $profile->id)->firstOrFail();
        $this->assertSame(['experience', 'skills'], $resume->sections);
        $this->assertSame($template->id, $resume->resume_template_id);

        $pdfResponse = $this->actingAs($user)
            ->get(route('admin.resumes.download', $resume))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
        $this->assertGreaterThan(1000, strlen($pdfResponse->getContent()));

        $resume->update(['format' => 'docx']);
        $docxResponse = $this->actingAs($user)
            ->get(route('admin.resumes.download', $resume))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $docxPath = $docxResponse->baseResponse->getFile()->getPathname();
        $document = new \ZipArchive;
        $this->assertSame(true, $document->open($docxPath));
        $documentXml = $document->getFromName('word/document.xml');
        $stylesXml = $document->getFromName('word/styles.xml');
        $document->close();

        $xmlDocument = new \DOMDocument;
        $previousLibxmlSetting = libxml_use_internal_errors(true);
        $isValidXml = $xmlDocument->loadXML($documentXml);
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlSetting);

        $this->assertTrue($isValidXml);
        $this->assertStringContainsString('Consultant ERP', $xmlDocument->textContent);
        $this->assertStringContainsString('Laravel', $xmlDocument->textContent);
        $this->assertStringContainsString('Consultant R&D & cybersécurité', $xmlDocument->textContent);
        $this->assertStringContainsString('E76F51', $stylesXml);
        $this->assertStringContainsString('Aptos', $stylesXml);
    }

    public function test_admin_cannot_download_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $owner->id]);
        $template = ResumeTemplate::query()->create(['name' => 'Classique', 'slug' => 'classic']);
        $resume = Resume::query()->create([
            'profile_id' => $profile->id,
            'title' => 'CV privé',
            'slug' => 'cv-prive',
            'resume_template_id' => $template->id,
            'sections' => [],
        ]);

        $this->actingAs($otherUser)
            ->get(route('admin.resumes.download', $resume))
            ->assertNotFound();
    }

    public function test_additional_templates_generate_pdf_and_docx(): void
    {
        $this->seed(\Database\Seeders\ResumeTemplateSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $user->id]);

        foreach (['editorial-nordic', 'portfolio-showcase', 'bold-header', 'graduate-focus', 'portfolio-sidebar', 'warm-academic'] as $slug) {
            $template = ResumeTemplate::query()->where('slug', $slug)->firstOrFail();
            $resume = Resume::query()->create([
                'profile_id' => $profile->id,
                'title' => 'CV '.$template->name,
                'slug' => 'cv-'.$slug,
                'resume_template_id' => $template->id,
                'locale' => 'fr',
                'length' => 'two_pages',
                'format' => 'pdf',
                'sections' => ['education', 'experience', 'skills'],
            ]);

            $this->actingAs($user)
                ->get(route('admin.resumes.download', $resume))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');

            $resume->update(['format' => 'docx']);
            $this->actingAs($user)
                ->get(route('admin.resumes.download', $resume))
                ->assertOk()
                ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        }
    }

    private function legacy_photo_upload_scenario(): void
    {
        $this->seed(\Database\Seeders\ResumeTemplateSeeder::class);
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $user->id, 'first_name' => 'Aminata']);
        $template = ResumeTemplate::query()->where('slug', 'portfolio-sidebar')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.resumes.store'), [
                'title' => 'CV avec portrait',
                'locale' => 'fr',
                'resume_template_id' => $template->id,
                'length' => 'two_pages',
                'format' => 'pdf',
                'sections' => ['education', 'experience'],
                'customization' => [
                    'include_photo' => true,
                    'photo' => UploadedFile::fake()->createWithContent(
                        'portrait.jpg',
                        base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/4gHYSUNDX1BST0ZJTEUAAQEAAAHIAAAAAAQwAABtbnRyUkdCIFhZWiAH4AABAAEAAAAAAABhY3NwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAA9tYAAQAAAADTLQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlkZXNjAAAA8AAAACRyWFlaAAABFAAAABRnWFlaAAABKAAAABRiWFlaAAABPAAAABR3dHB0AAABUAAAABRyVFJDAAABZAAAAChnVFJDAAABZAAAAChiVFJDAAABZAAAAChjcHJ0AAABjAAAADxtbHVjAAAAAAAAAAEAAAAMZW5VUwAAAAgAAAAcAHMAUgBHAEJYWVogAAAAAAAAb6IAADj1AAADkFhZWiAAAAAAAABimQAAt4UAABjaWFlaIAAAAAAAACSgAAAPhAAAts9YWVogAAAAAAAA9tYAAQAAAADTLXBhcmEAAAAAAAQAAAACZmYAAPKnAAANWQAAE9AAAApbAAAAAAAAAABtbHVjAAAAAAAAAAEAAAAMZW5VUwAAACAAAAAcAEcAbwBvAGcAbABlACAASQBuAGMALgAgADIAMAAxADb/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAABP/EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AIoAWK//2Q=='),
                    ),
                ],
            ])
            ->assertRedirect(route('admin.resumes.index'));

        $resume = Resume::query()->where('profile_id', $profile->id)->firstOrFail();
        $photoPath = $resume->customization['photo_path'] ?? null;

        $this->assertNotEmpty($photoPath);
        Storage::disk('public')->assertExists($photoPath);
        $this->assertTrue($resume->customization['include_photo']);

        $replacement = UploadedFile::fake()->createWithContent('replacement.jpg', Storage::disk('public')->get($photoPath));
        $this->actingAs($user)
            ->put(route('admin.resumes.update', $resume), [
                'title' => 'CV avec portrait remplace',
                'locale' => 'fr',
                'resume_template_id' => $template->id,
                'length' => 'two_pages',
                'format' => 'pdf',
                'sections' => ['education', 'experience'],
                'customization' => ['include_photo' => true, 'photo' => $replacement],
            ])
            ->assertRedirect(route('admin.resumes.index'));
        $resume->refresh();
        $replacementPath = $resume->customization['photo_path'];
        $this->assertNotSame($photoPath, $replacementPath);
        Storage::disk('public')->assertMissing($photoPath);
        Storage::disk('public')->assertExists($replacementPath);
        $photoPath = $replacementPath;

        $pdfResponse = $this->actingAs($user)
            ->get(route('admin.resumes.download', $resume))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('/Subtype /Image', $pdfResponse->getContent());

        $resume->update(['format' => 'docx']);
        $docxResponse = $this->actingAs($user)
            ->get(route('admin.resumes.download', $resume))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $document = new \ZipArchive;
        $this->assertSame(true, $document->open($docxResponse->baseResponse->getFile()->getPathname()));
        $embeddedImages = [];
        for ($index = 0; $index < $document->numFiles; $index++) {
            $name = $document->getNameIndex($index);
            if (str_starts_with($name, 'word/media/')) $embeddedImages[] = $name;
        }
        $document->close();
        $this->assertNotEmpty($embeddedImages);

        $this->actingAs($user)->delete(route('admin.resumes.destroy', $resume))->assertRedirect(route('admin.resumes.index'));
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_existing_profile_photo_is_used_by_resume_content(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Aminata',
            'avatar_path' => 'avatars/aminata.jpg',
        ]);
        $template = ResumeTemplate::query()->create([
            'name' => 'Portfolio Sidebar',
            'slug' => 'portfolio-sidebar-photo-test',
            'layout' => 'portfolio-sidebar',
        ]);
        $resume = Resume::query()->create([
            'profile_id' => $profile->id,
            'title' => 'CV avec avatar existant',
            'slug' => 'cv-avatar-existant',
            'resume_template_id' => $template->id,
            'customization' => ['include_photo' => true],
        ]);

        $content = app(\App\Services\ResumeContentBuilder::class)->build($resume->load('profile'));

        $this->assertSame('avatars/aminata.jpg', $content['profile']['avatar_path']);
    }

    public function test_latest_existing_jpeg_in_homepage_slides_is_used_when_profile_has_no_avatar(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $user->id]);
        $workPhoto = Media::query()->create([
            'title' => 'Photo de travail',
            'file_name' => 'travail.jpg',
            'file_path' => 'homepage-slides/travail.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
            'category' => 'homepage_slider',
            'alt_text' => 'Photo de travail',
            'sort_order' => 1,
            'is_public' => true,
        ]);
        Media::query()->create([
            'title' => 'Portrait existant',
            'file_name' => 'portrait.jpg',
            'file_path' => 'homepage-slides/portrait.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
            'category' => 'homepage_slider',
            'alt_text' => 'Portrait existant',
            'sort_order' => 2,
            'is_public' => true,
        ]);
        $template = ResumeTemplate::query()->create([
            'name' => 'Portfolio Sidebar',
            'slug' => 'portfolio-sidebar-media-test',
            'layout' => 'portfolio-sidebar',
        ]);
        $this->actingAs($user)
            ->post(route('admin.resumes.store'), [
                'title' => 'CV depuis les médias existants',
                'locale' => 'fr',
                'resume_template_id' => $template->id,
                'length' => 'two_pages',
                'format' => 'pdf',
                'sections' => ['experience'],
                'customization' => ['include_photo' => true, 'photo_media_id' => $workPhoto->id],
            ])
            ->assertRedirect(route('admin.resumes.index'));
        $resume = Resume::query()->where('profile_id', $profile->id)->firstOrFail();
        $this->assertSame($workPhoto->id, $resume->customization['photo_media_id']);

        $content = app(\App\Services\ResumeContentBuilder::class)->build($resume->load('profile'));

        $this->assertSame('homepage-slides/travail.jpg', $content['profile']['avatar_path']);
        $resume->update(['customization' => ['include_photo' => true]]);
        $contentWithAutomaticPhoto = app(\App\Services\ResumeContentBuilder::class)->build($resume->load('profile'));
        $this->assertSame('homepage-slides/portrait.jpg', $contentWithAutomaticPhoto['profile']['avatar_path']);
        $resume->update(['customization' => ['include_photo' => false]]);
        $contentWithoutPhoto = app(\App\Services\ResumeContentBuilder::class)->build($resume->load('profile'));
        $this->assertNull($contentWithoutPhoto['profile']['avatar_path']);
    }

    public function test_resume_creation_persists_targeted_content_and_design_preferences(): void
    {
        $this->seed(\Database\Seeders\ResumeTemplateSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $user->id]);
        $experience = Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Cabinet Horizon',
            'position' => 'Consultante',
        ]);
        $skill = Skill::query()->create(['name' => ['fr' => 'Sage X3', 'en' => 'Sage X3']]);
        $template = ResumeTemplate::query()->where('slug', 'consultant')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.resumes.store'), [
                'title' => 'CV Consultant Sage X3',
                'locale' => 'fr',
                'resume_template_id' => $template->id,
                'length' => 'one_page',
                'format' => 'pdf',
                'sections' => ['experience', 'skills'],
                'content_selection' => [
                    'experiences' => [$experience->id],
                    'educations' => [],
                    'skills' => [$skill->id],
                    'projects' => [],
                    'certifications' => [],
                    'languages' => [],
                ],
                'customization' => [
                    'palette' => 'custom',
                    'use_custom_colors' => true,
                    'primary_color' => '#123456',
                    'secondary_color' => '#654321',
                    'font' => 'serif',
                    'font_size' => 11,
                ],
                'target_role' => 'Consultante Sage X3',
                'target_market' => 'international',
                'summary_override' => 'Résumé ciblé.',
            ])
            ->assertRedirect(route('admin.resumes.index'));

        $resume = Resume::query()->where('profile_id', $profile->id)->firstOrFail();
        $this->assertSame('Consultante Sage X3', $resume->target_role);
        $this->assertSame('international', $resume->target_market);
        $this->assertSame([$experience->id], $resume->content_selection['experiences']);
        $this->assertSame([$skill->id], $resume->content_selection['skills']);
        $this->assertSame('#123456', $resume->customization['primary_color']);
        $this->assertSame('Résumé ciblé.', $resume->summary_override);
    }

    public function test_admin_can_view_edit_and_delete_own_resume(): void
    {
        $this->seed(\Database\Seeders\ResumeTemplateSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $user->id]);
        $template = ResumeTemplate::query()->where('slug', 'classic-professional')->firstOrFail();
        $resume = Resume::query()->create([
            'profile_id' => $profile->id,
            'title' => 'CV initial',
            'slug' => 'cv-initial',
            'resume_template_id' => $template->id,
            'locale' => 'fr',
            'length' => 'one_page',
            'format' => 'pdf',
            'sections' => ['experience'],
        ]);

        $this->actingAs($user)
            ->get(route('admin.resumes.index'))
            ->assertOk()
            ->assertSee('data-resume-edit-url', false)
            ->assertSee('resumePreviewModal')
            ->assertSee('resumeEditModal')
            ->assertSee('resumeDeleteModal');
        $this->actingAs($user)
            ->get(route('admin.resumes.show', ['resume' => $resume, 'modal' => 1]))
            ->assertOk()
            ->assertSee('CV initial')
            ->assertDontSee('<nav class="screen-actions">', false);
        $this->actingAs($user)
            ->get(route('admin.resumes.edit-modal', $resume))
            ->assertOk()
            ->assertSee('name="title"', false)
            ->assertSee('data-resume-edit-form', false);
        $this->actingAs($user)
            ->put(route('admin.resumes.update', $resume), [
                'title' => 'CV mis à jour',
                'locale' => 'en',
                'resume_template_id' => $template->id,
                'length' => 'one_page',
                'format' => 'docx',
                'sections' => ['skills', 'projects'],
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message', 'CV mis à jour.');
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'title' => 'CV mis à jour', 'locale' => 'en', 'format' => 'docx']);
        $this->assertSame(['skills', 'projects'], $resume->fresh()->sections);
        $this->actingAs($user)
            ->delete(route('admin.resumes.destroy', $resume), [], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message', 'CV supprimé.');
        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
    }

    public function test_admin_cannot_view_or_edit_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $owner->id]);
        $template = ResumeTemplate::query()->create(['name' => 'Classique', 'slug' => 'classic']);
        $resume = Resume::query()->create([
            'profile_id' => $profile->id,
            'title' => 'CV privé',
            'slug' => 'cv-prive',
            'resume_template_id' => $template->id,
            'sections' => [],
        ]);

        $this->actingAs($otherUser)
            ->get(route('admin.resumes.show', $resume))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->get(route('admin.resumes.edit', $resume))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->get(route('admin.resumes.edit-modal', $resume))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->delete(route('admin.resumes.destroy', $resume))
            ->assertNotFound();

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'title' => 'CV privé']);
    }
}
