<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Service;
use App\Models\Section;
use App\Models\SocialLink;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_public_home_page_loads_with_profile_education(): void
    {
        $user = User::factory()->create();
        $profile = Profile::query()->create(['user_id' => $user->id]);
        Education::query()->create([
            'profile_id' => $profile->id,
            'school' => 'Université de test',
            'degree' => 'Licence',
        ]);

        $this->get('/')->assertOk();
    }

    public function test_public_experience_is_visible_when_sections_have_not_been_configured(): void
    {
        $user = User::factory()->create();
        $profile = Profile::query()->create(['user_id' => $user->id]);
        Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'GROUPE LOGICIEL & SERVICES',
            'position' => 'Consultant Sage X3',
            'published' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('GROUPE LOGICIEL & SERVICES')
            ->assertSee('Consultant Sage X3')
            ->assertSee('href="#experience"', false);
    }

    public function test_all_public_certifications_share_the_horizontal_carousel(): void
    {
        Section::query()->create([
            'key' => 'certifications',
            'title' => ['fr' => 'Certifications', 'en' => 'Certifications'],
            'enabled' => true,
            'sort_order' => 1,
        ]);

        foreach (['Cyber Threat Management', 'Junior Cybersecurity Analyst', 'Network Defense'] as $title) {
            Certification::query()->create([
                'title' => ['fr' => $title, 'en' => $title],
                'issuer' => 'Cisco Networking Academy',
                'issue_date' => '2024-12-09',
                'published' => true,
            ]);
        }

        $response = $this->get('/');
        $response->assertOk()->assertSee('data-portfolio-carousel', false);

        $html = $response->getContent();
        $this->assertSame(3, substr_count($html, 'class="portfolio-carousel-slide portfolio-certification-slide portfolio-compact-slide"'));
        $this->assertStringNotContainsString('portfolio-certification-grid', $html);
    }

    public function test_public_services_share_the_compact_horizontal_card_layout(): void
    {
        Section::query()->create([
            'key' => 'services',
            'title' => ['fr' => 'Services', 'en' => 'Services'],
            'enabled' => true,
            'sort_order' => 1,
        ]);

        foreach (['Service alpha', 'Service beta', 'Service gamma'] as $title) {
            Service::query()->create([
                'title' => ['fr' => $title, 'en' => $title],
                'description' => ['fr' => 'Description du service.', 'en' => 'Service description.'],
                'icon' => '⚙️',
                'published' => true,
            ]);
        }

        $response = $this->get('/');
        $response->assertOk()->assertSee('data-portfolio-carousel', false);

        $html = $response->getContent();
        $this->assertSame(3, substr_count($html, 'class="portfolio-carousel-slide portfolio-service-slide portfolio-compact-slide"'));
        $this->assertSame(3, substr_count($html, 'class="portfolio-panel portfolio-service-card"'));
    }

    public function test_public_social_links_use_the_compact_brand_icon_cards(): void
    {
        $user = User::factory()->create();
        $profile = Profile::query()->create(['user_id' => $user->id]);

        foreach ([
            ['platform' => 'GitHub', 'url' => 'https://github.com/Alino999', 'icon' => 'github'],
            ['platform' => 'LinkedIn', 'url' => 'https://linkedin.com/in/alain-guigma-841758205', 'icon' => 'linkedin'],
            ['platform' => 'Facebook', 'url' => 'https://www.facebook.com/alain.guigma.90/', 'icon' => 'facebook'],
        ] as $order => $socialLink) {
            SocialLink::query()->create($socialLink + [
                'profile_id' => $profile->id,
                'label' => $socialLink['platform'],
                'is_active' => true,
                'sort_order' => $order + 1,
            ]);
        }

        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('bi-github', false)
            ->assertSee('bi-linkedin', false)
            ->assertSee('bi-facebook', false);

        $html = $response->getContent();
        $this->assertSame(3, substr_count($html, 'class="portfolio-carousel-slide portfolio-social-slide portfolio-compact-slide"'));
        $this->assertSame(3, substr_count($html, 'class="portfolio-panel portfolio-social-card"'));
    }

    public function test_selected_locale_persists_after_switching_back_to_home(): void
    {
        $this->get(route('locale.switch', ['locale' => 'fr']))
            ->assertRedirect('/');

        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="fr">', false)
            ->assertSee('Envoyer');
    }

    public function test_blog_article_links_open_in_modal_on_home_page(): void
    {
        Section::query()->create([
            'key' => 'blog',
            'title' => ['fr' => 'Blog', 'en' => 'Blog'],
            'enabled' => true,
            'sort_order' => 1,
        ]);

        Article::query()->create([
            'title' => ['fr' => 'Article bêta', 'en' => 'Beta article'],
            'slug' => 'article-beta',
            'excerpt' => ['fr' => 'Extrait', 'en' => 'Excerpt'],
            'content' => ['fr' => 'Contenu complet de l’article.', 'en' => 'Full article content.'],
            'status' => 'published',
            'published_at' => now(),
            'featured' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-bs-toggle="modal"', false)
            ->assertSee('article-modal-', false)
            ->assertSee('portfolio-blog-card', false)
            ->assertSee('Like')
            ->assertSee('Comments')
            ->assertSee('article-beta/comments');
    }

    public function test_homepage_blog_shows_only_five_most_recently_registered_articles(): void
    {
        Section::query()->create([
            'key' => 'blog',
            'title' => ['fr' => 'Blog', 'en' => 'Blog'],
            'enabled' => true,
            'sort_order' => 1,
        ]);

        for ($number = 1; $number <= 6; $number++) {
            $article = Article::query()->create([
                'title' => ['fr' => "Article $number", 'en' => "Article $number"],
                'slug' => "article-$number",
                'excerpt' => ['fr' => 'Extrait', 'en' => 'Excerpt'],
                'content' => ['fr' => 'Contenu détaillé.', 'en' => 'Detailed content.'],
                'status' => 'published',
                'published_at' => now()->subDays(7 - $number),
                'featured' => false,
            ]);
            $article->forceFill([
                'created_at' => now()->subDays($number),
                'updated_at' => now()->subDays($number),
            ])->saveQuietly();
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('Article 1')
            ->assertDontSee('Article 6');
    }

    public function test_public_testimonial_requires_admin_approval_before_display(): void
    {
        Section::query()->create([
            'key' => 'testimonials',
            'title' => ['fr' => 'Témoignages', 'en' => 'Testimonials'],
            'enabled' => true,
            'sort_order' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-testimonial-form', false)
            ->assertSee('data-bs-target="#testimonial-modal"', false)
            ->assertSee('id="testimonial-modal"', false)
            ->assertSee('id="testimonial-rating-5"', false)
            ->assertDontSee('<select id="testimonial-rating"', false);

        $this->post(route('public.testimonials.store'), [
            'author' => 'Client satisfait',
            'role' => 'Directeur',
            'company' => 'Entreprise test',
            'content' => 'Une excellente collaboration avec Alain.',
            'rating' => 3,
        ])->assertRedirect(route('home').'#testimonials');

        $testimonial = Testimonial::query()->where('author', 'Client satisfait')->firstOrFail();
        $this->assertFalse($testimonial->published);
        $this->assertSame(3, $testimonial->rating);
        $this->get('/')->assertDontSee('Une excellente collaboration avec Alain.');

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($admin)
            ->patch(route('admin.testimonials.toggle-publication', $testimonial))
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertTrue($testimonial->fresh()->published);
        $this->get('/')
            ->assertSee('Une excellente collaboration avec Alain.')
            ->assertSee('class="testimonial-rating-display"', false)
            ->assertSee('aria-label="3 / 5"', false);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertStatus(200);
    }
}
