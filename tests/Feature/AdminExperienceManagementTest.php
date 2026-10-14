<?php

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminExperienceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_experiences_management(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.experiences.index'))
            ->assertOk();
    }

    public function test_admin_can_create_a_bilingual_experience(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'first_name' => 'Lino',
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.experiences.store'), [
                'company' => 'Cabinet IT',
                'company_en' => 'IT Consulting Firm',
                'position' => 'Consultant ERP',
                'position_en' => 'ERP Consultant',
                'location' => 'Ouagadougou',
                'contract_type' => 'CDI',
                'start_date' => '2024-01-01',
                'is_current' => true,
                'description_fr' => 'Accompagnement des entreprises.',
                'description_en' => 'Supporting businesses.',
                'responsibilities_fr' => "Analyse des besoins\nConfiguration ERP",
                'responsibilities_en' => "Needs analysis\nERP configuration",
                'achievements_fr' => 'Réduction des délais.',
                'achievements_en' => 'Reduced delivery times.',
                'technologies' => "Sage X3\nSQL",
                'image' => UploadedFile::fake()->createWithContent('experience.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')),
                'sort_order' => 1,
                'published' => true,
            ]);

        $response->assertRedirect(route('admin.experiences.index'));
        $this->assertDatabaseHas('experiences', [
            'profile_id' => $profile->id,
            'company' => 'Cabinet IT',
            'company_en' => 'IT Consulting Firm',
            'position' => 'Consultant ERP',
            'position_en' => 'ERP Consultant',
            'published' => 1,
        ]);
        $this->assertTrue(Experience::query()->where('description->en', 'Supporting businesses.')->exists());
        $experience = Experience::query()->where('company', 'Cabinet IT')->firstOrFail();
        $this->assertSame(['Needs analysis', 'ERP configuration'], $experience->responsibilities['en']);
        Storage::disk('public')->assertExists($experience->image_path);
    }

    public function test_admin_can_edit_and_delete_an_experience(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $profile = Profile::query()->create(['user_id' => $user->id]);
        $experience = Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Ancienne entreprise',
            'position' => 'Consultant',
            'start_date' => '2023-01-01',
            'is_current' => true,
            'description' => ['fr' => ['Accompagnement des entreprises.'], 'en' => ['Supporting businesses.']],
            'responsibilities' => ['fr' => ['Analyse des besoins'], 'en' => ['Needs analysis']],
            'achievements' => ['fr' => ['Réduction des délais'], 'en' => ['Reduced delays']],
        ]);

        $this->actingAs($user)
            ->get(route('admin.experiences.edit', $experience))
            ->assertOk()
            ->assertSee('Accompagnement des entreprises.')
            ->assertSee('Analyse des besoins')
            ->assertSee('Needs analysis');

        $this->actingAs($user)
            ->put(route('admin.experiences.update', $experience), [
                'company' => 'Nouvelle entreprise',
                'position' => 'Consultant senior',
                'start_date' => '2023-01-01',
                'end_date' => '2024-12-31',
                'description_fr' => 'Nouvelle description',
                'description_en' => 'Updated description',
                'published' => true,
            ])
            ->assertRedirect(route('admin.experiences.index'));

        $this->assertDatabaseHas('experiences', [
            'id' => $experience->id,
            'company' => 'Nouvelle entreprise',
            'position' => 'Consultant senior',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.experiences.destroy', $experience))
            ->assertRedirect(route('admin.experiences.index'));

        $this->assertDatabaseMissing('experiences', ['id' => $experience->id]);
    }

    public function test_admin_cannot_manage_another_users_experience(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $profile = Profile::query()->create(['user_id' => $owner->id]);
        $experience = Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Entreprise privée',
            'position' => 'Consultant',
        ]);

        $this->actingAs($otherUser)
            ->get(route('admin.experiences.edit', $experience))
            ->assertNotFound();
    }

    public function test_bulk_actions_cannot_change_another_users_experience(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $owner->id]);
        $experience = Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Entreprise privée',
            'position' => 'Consultant',
            'published' => false,
        ]);

        $this->actingAs($otherUser)
            ->post(route('admin.bulk-action', 'experiences'), [
                'action' => 'publish',
                'ids' => [$experience->id],
            ])
            ->assertNotFound();

        $this->assertFalse($experience->fresh()->published);
    }

    public function test_dashboard_does_not_show_another_users_experience_activity(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $owner->id]);
        Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Private account company',
            'position' => 'Private account position',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Private account company')
            ->assertDontSee('Private account position');
    }
}
