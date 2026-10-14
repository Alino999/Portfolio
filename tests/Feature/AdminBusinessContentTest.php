<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBusinessContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_services(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('admin.services.index'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('admin.services.store'), [
                'title_fr' => 'Conseil ERP',
                'title_en' => 'ERP Consulting',
                'description_fr' => 'Accompagnement des entreprises.',
                'description_en' => 'Business support.',
                'icon' => 'briefcase',
                'image' => UploadedFile::fake()->createWithContent('service.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')),
                'sort_order' => 1,
                'published' => true,
            ])
            ->assertRedirect(route('admin.services.index'));

        $service = Service::query()->where('title->fr', 'Conseil ERP')->firstOrFail();
        $this->assertSame('ERP Consulting', $service->title['en']);
        Storage::disk('public')->assertExists($service->image_path);

        $this->actingAs($user)
            ->get(route('admin.services.edit', $service))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('admin.services.update', $service), [
                'title_fr' => 'Conseil ERP avancé',
                'title_en' => 'Advanced ERP Consulting',
                'description_fr' => 'Description mise à jour.',
                'description_en' => 'Updated description.',
                'sort_order' => 2,
                'published' => true,
            ])
            ->assertRedirect(route('admin.services.index'));

        $this->assertTrue(Service::query()->where('title->fr', 'Conseil ERP avancé')->exists());

        $this->actingAs($user)
            ->delete(route('admin.services.destroy', $service))
            ->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_admin_can_bulk_publish_and_delete_selected_services(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $services = collect(range(1, 3))->map(fn (int $number) => Service::query()->create([
            'title' => ['fr' => "Service $number", 'en' => "Service $number"],
            'description' => [],
            'sort_order' => $number,
            'published' => false,
        ]));

        $this->actingAs($user)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('data-bulk-select-all', false);

        $this->post(route('admin.bulk-action', 'services'), [
            'action' => 'publish',
            'ids' => [$services[0]->id, $services[1]->id],
        ])->assertRedirect(route('admin.services.index'));

        $this->assertTrue($services[0]->fresh()->published);
        $this->assertTrue($services[1]->fresh()->published);
        $this->assertFalse($services[2]->fresh()->published);

        $this->post(route('admin.bulk-action', 'services'), [
            'action' => 'delete',
            'ids' => [$services[0]->id, $services[1]->id],
        ])->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseMissing('services', ['id' => $services[0]->id]);
        $this->assertDatabaseMissing('services', ['id' => $services[1]->id]);
        $this->assertDatabaseHas('services', ['id' => $services[2]->id]);
    }

    public function test_admin_can_manage_testimonials(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('admin.testimonials.index'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('admin.testimonials.store'), [
                'author' => 'Client test',
                'role' => 'Directeur',
                'company' => 'Entreprise test',
                'content_fr' => 'Une collaboration remarquable.',
                'content_en' => 'An outstanding collaboration.',
                'rating' => 5,
                'sort_order' => 1,
                'published' => true,
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $testimonial = Testimonial::query()->where('author', 'Client test')->firstOrFail();
        $this->assertSame('An outstanding collaboration.', $testimonial->content['en']);

        $this->actingAs($user)
            ->get(route('admin.testimonials.edit', $testimonial))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('admin.testimonials.update', $testimonial), [
                'author' => 'Client vérifié',
                'role' => 'Directeur général',
                'company' => 'Entreprise test',
                'content_fr' => 'Une collaboration encore meilleure.',
                'content_en' => 'An even better collaboration.',
                'rating' => 4,
                'sort_order' => 2,
                'published' => true,
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'author' => 'Client vérifié', 'rating' => 4]);

        $this->actingAs($user)
            ->delete(route('admin.testimonials.destroy', $testimonial))
            ->assertRedirect(route('admin.testimonials.index'));
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }
}
