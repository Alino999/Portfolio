<?php

namespace Tests\Feature;

use App\Models\ResumeTemplate;
use App\Models\ResumeTemplateCategory;
use App\Models\User;
use Database\Seeders\ResumeTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminResumeTemplateLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::query()->firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }

    public function test_super_admin_can_browse_the_seeded_template_library(): void
    {
        $this->seed(ResumeTemplateSeeder::class);
        $user = $this->superAdmin();
        $secondPageTemplateName = ResumeTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->skip(10)
            ->value('name');

        $this->actingAs($user)
            ->get(route('admin.resume-templates.index'))
            ->assertOk()
            ->assertSee('Classic Professional')
            ->assertSee('ATS Clean')
            ->assertSee('name="blocks[]" value="photo"', false)
            ->assertSee('ATS Friendly')
            ->assertSee('Prévisualiser')
            ->assertSee('page=2', false);

        $this->actingAs($user)
            ->get(route('admin.resume-templates.index', ['page' => 2]))
            ->assertOk()
            ->assertSee($secondPageTemplateName);
    }

    public function test_super_admin_can_create_duplicate_toggle_templates_and_manage_categories(): void
    {
        $this->seed(ResumeTemplateSeeder::class);
        $user = $this->superAdmin();
        $category = ResumeTemplateCategory::query()->where('slug', 'informatique')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.resume-template-categories.store'), [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Finance and accounting profiles.',
                'sort_order' => 20,
            ])
            ->assertRedirect(route('admin.resume-template-categories.index'));

        $this->assertDatabaseHas('resume_template_categories', ['slug' => 'finance']);

        $this->actingAs($user)
            ->post(route('admin.resume-templates.store'), [
                'name' => 'IT Security Custom',
                'slug' => 'it-security-custom',
                'description' => 'Custom security resume.',
                'category_id' => $category->id,
                'style' => 'technical',
                'layout' => 'tech',
                'ats_friendly' => true,
                'languages' => ['fr', 'en'],
                'recommended_pages' => 'two_pages',
                'available_formats' => ['pdf', 'docx'],
                'blocks' => ['header', 'experience', 'skills'],
                'sort_order' => 30,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.resume-templates.index'));

        $template = ResumeTemplate::query()->where('slug', 'it-security-custom')->firstOrFail();
        $this->assertTrue($template->ats_friendly);
        $this->assertSame(['header', 'experience', 'skills'], $template->blocks);

        $this->actingAs($user)
            ->post(route('admin.resume-templates.store'), [
                'name' => 'IT Security with photo',
                'slug' => 'it-security-with-photo',
                'category_id' => $category->id,
                'style' => 'technical',
                'layout' => 'tech',
                'languages' => ['fr'],
                'recommended_pages' => 'two_pages',
                'available_formats' => ['pdf'],
                'blocks' => ['header', 'photo', 'experience'],
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.resume-templates.index'));

        $this->assertTrue(ResumeTemplate::query()->where('slug', 'it-security-with-photo')->firstOrFail()->supportsPhoto());
        $this->assertFalse($template->supportsPhoto());

        $this->actingAs($user)
            ->post(route('admin.resume-templates.duplicate', $template))
            ->assertRedirect(route('admin.resume-templates.index'));

        $this->assertDatabaseHas('resume_templates', ['slug' => 'it-security-custom-copy']);

        $this->actingAs($user)
            ->patch(route('admin.resume-templates.toggle', $template))
            ->assertRedirect(route('admin.resume-templates.index'));

        $this->assertDatabaseHas('resume_templates', ['id' => $template->id, 'is_active' => 0]);
    }

    public function test_super_admin_cannot_bulk_delete_system_templates(): void
    {
        $this->seed(ResumeTemplateSeeder::class);
        $user = $this->superAdmin();
        $template = ResumeTemplate::query()->where('is_system', true)->firstOrFail();

        $this->actingAs($user)
            ->from(route('admin.resume-templates.index'))
            ->post(route('admin.bulk-action', 'resume-templates'), [
                'action' => 'delete',
                'ids' => [$template->id],
            ])
            ->assertSessionHasErrors('ids');

        $this->assertDatabaseHas('resume_templates', ['id' => $template->id]);
    }

    public function test_non_super_admin_cannot_access_template_library_management(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('admin.resume-templates.index'))
            ->assertForbidden();
    }
}
