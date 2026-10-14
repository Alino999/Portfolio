<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_projects_management(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.projects.index'))
            ->assertOk();
    }

    public function test_admin_can_create_a_project(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.projects.store'), [
                'title_fr' => 'Portfolio Laravel',
                'title_en' => 'Laravel Portfolio',
                'short_description_fr' => 'Portfolio professionnel moderne',
                'short_description_en' => 'Modern professional portfolio',
                'description_fr' => 'Développement d’un portfolio complet et administrable.',
                'description_en' => 'Development of a complete and manageable portfolio.',
                'image' => UploadedFile::fake()->createWithContent('project.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')),
                'client' => 'Lino',
                'role' => 'Développeur full stack',
                'status' => 'published',
                'project_type' => 'professional',
                'sort_order' => 1,
                'published' => true,
            ]);

        $response->assertRedirect(route('admin.projects.index'));
        $this->assertDatabaseHas('projects', [
            'client' => 'Lino',
            'status' => 'published',
            'published' => 1,
        ]);
        $this->assertTrue(Project::query()->where('title->fr', 'Portfolio Laravel')->exists());
        $project = Project::query()->where('title->fr', 'Portfolio Laravel')->firstOrFail();
        Storage::disk('public')->assertExists($project->image_path);
    }

    public function test_editing_a_project_preserves_an_uploaded_image_without_an_external_url(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('admin.projects.store'), [
                'title_fr' => 'Projet photo',
                'title_en' => 'Image project',
                'project_date' => '2026-02-10',
                'image' => UploadedFile::fake()->createWithContent('project.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')),
                'published' => true,
            ])
            ->assertRedirect(route('admin.projects.index'));

        $project = Project::query()->where('title->fr', 'Projet photo')->firstOrFail();
        $imagePath = $project->image_path;
        $this->get(route('admin.projects.edit', $project))->assertOk();

        $this->put(route('admin.projects.update', $project), [
            'title_fr' => 'Projet photo modifié',
            'title_en' => 'Updated image project',
            'image_path' => '',
            'published' => true,
        ])->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'image_path' => $imagePath]);
        Storage::disk('public')->assertExists($imagePath);
    }
}
