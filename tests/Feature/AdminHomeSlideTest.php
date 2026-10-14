<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHomeSlideTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_multiple_homepage_slides_and_they_render_on_homepage(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('admin.home-slides.store'), [
                'photos' => [
                    $this->fakeImage('photo-one.png'),
                    $this->fakeImage('photo-two.png'),
                ],
            ])
            ->assertRedirect(route('admin.home-slides.index'));

        $slides = Media::query()->where('category', 'homepage_slider')->orderBy('sort_order')->get();
        $this->assertCount(2, $slides);

        foreach ($slides as $slide) {
            Storage::disk('public')->assertExists($slide->file_path);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-photo-slider')
            ->assertSee('photo-one')
            ->assertSee('photo-two');
    }

    public function test_admin_can_remove_a_homepage_slide(): void
    {
        Storage::fake('public');
        $path = 'homepage-slides/photo.jpg';
        Storage::disk('public')->put($path, 'image contents');
        $slide = Media::query()->create([
            'file_name' => 'photo.jpg',
            'file_path' => $path,
            'mime_type' => 'image/jpeg',
            'file_size' => 14,
            'category' => 'homepage_slider',
            'sort_order' => 1,
            'is_public' => true,
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->delete(route('admin.home-slides.destroy', $slide))
            ->assertRedirect(route('admin.home-slides.index'));

        $this->assertDatabaseMissing('media', ['id' => $slide->id]);
        Storage::disk('public')->assertMissing($path);
    }

    private function fakeImage(string $name): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/2ioAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($name, $contents);
    }
}