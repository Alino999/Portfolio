<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Section;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'super-admin']);

        $user = User::firstOrCreate(
            ['email' => 'guigmaa507@gmail.com'],
            [
                'name' => 'ALAIN GUIGMA',
                'password' => bcrypt('Lino@2026'),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => 'GUIGMA',
                'last_name' => 'ALAIN',
                'title' => 'Consultant Sage X3 & cybersécurité & IT',
                'subtitle' => 'Consultant informatique · ERP · sécurité · développement · IT',
                'summary' => 'Consultant spécialisé en ERP, cybersécurité et développement informatique.',
                'bio' => 'Je conçois des solutions informatiques fiables, sécurisées et orientées résultats pour les entreprises.',
                'email' => 'guigmaa507@gmail.com',
                'phone' => '+226 66 41 07 68',
                'location' => 'Ouagadougou, Burkina Faso',
                'country' => 'Burkina Faso',
                'availability' => 'Disponible',
                'professional_status' => 'Consultant senior',
                'years_experience' => 7,
                'website' => 'https://example.com',
                'linkedin' => 'https://linkedin.com',
                'github' => 'https://github.com',
                'primary_skills' => ['Sage X3', 'Cybersécurité', 'Laravel'],
                'is_public' => true,
            ]
        );

        SocialLink::updateOrCreate(
            ['profile_id' => $profile->id, 'platform' => 'LinkedIn'],
            ['label' => 'LinkedIn', 'url' => 'https://linkedin.com', 'icon' => 'linkedin', 'is_active' => true, 'sort_order' => 1]
        );

        Section::updateOrCreate(['key' => 'about'], ['title' => ['fr' => 'À propos', 'en' => 'About'], 'enabled' => true, 'sort_order' => 1]);
        Section::updateOrCreate(['key' => 'skills'], ['title' => ['fr' => 'Compétences', 'en' => 'Skills'], 'enabled' => true, 'sort_order' => 2]);
        Section::updateOrCreate(['key' => 'experience'], ['title' => ['fr' => 'Expérience', 'en' => 'Experience'], 'enabled' => true, 'sort_order' => 3]);
        Section::updateOrCreate(['key' => 'services'], ['title' => ['fr' => 'Services', 'en' => 'Services'], 'enabled' => true, 'sort_order' => 4]);
        Section::updateOrCreate(['key' => 'projects'], ['title' => ['fr' => 'Projets', 'en' => 'Projects'], 'enabled' => true, 'sort_order' => 5]);
        Section::updateOrCreate(['key' => 'certifications'], ['title' => ['fr' => 'Certifications', 'en' => 'Certifications'], 'enabled' => true, 'sort_order' => 6]);
        Section::updateOrCreate(['key' => 'blog'], ['title' => ['fr' => 'Blog', 'en' => 'Blog'], 'enabled' => true, 'sort_order' => 7]);
        Section::updateOrCreate(['key' => 'testimonials'], ['title' => ['fr' => 'Témoignages', 'en' => 'Testimonials'], 'enabled' => true, 'sort_order' => 8]);

        SiteSetting::updateOrCreate(['key' => 'site_name'], ['value' => json_encode('Portfolio Lino')]);
        SiteSetting::updateOrCreate(['key' => 'site_email'], ['value' => json_encode('guigmaa507@gmail.com')]);
        SiteSetting::updateOrCreate(['key' => 'site_phone'], ['value' => json_encode('+226 66 41 07 68')]);
        SiteSetting::updateOrCreate(['key' => 'site_location'], ['value' => json_encode('Ouagadougou, Burkina Faso')]);
        SiteSetting::updateOrCreate(['key' => 'default_locale'], ['value' => json_encode('fr')]);
        SiteSetting::updateOrCreate(['key' => 'primary_color'], ['value' => json_encode('#0d6efd')]);

        $erpCategory = SkillCategory::firstOrCreate(['slug' => 'erp'], ['name' => ['fr' => 'ERP', 'en' => 'ERP'], 'sort_order' => 1, 'published' => true]);
        $cyberCategory = SkillCategory::firstOrCreate(['slug' => 'cybersecurity'], ['name' => ['fr' => 'Cybersécurité', 'en' => 'Cybersecurity'], 'sort_order' => 2, 'published' => true]);
        $devCategory = SkillCategory::firstOrCreate(['slug' => 'development'], ['name' => ['fr' => 'Développement', 'en' => 'Development'], 'sort_order' => 3, 'published' => true]);

        Skill::updateOrCreate(['name' => ['fr' => 'Sage X3', 'en' => 'Sage X3']], ['skill_category_id' => $erpCategory->id, 'level' => 'Expert', 'percentage' => 90, 'years_experience' => 5, 'description' => ['fr' => 'Administration et optimisation Sage X3', 'en' => 'Sage X3 administration and optimization'], 'sort_order' => 1, 'published' => true]);
        Skill::updateOrCreate(['name' => ['fr' => 'Sécurité des SI', 'en' => 'Information Security']], ['skill_category_id' => $cyberCategory->id, 'level' => 'Expert', 'percentage' => 85, 'years_experience' => 4, 'sort_order' => 2, 'published' => true]);
        Skill::updateOrCreate(['name' => ['fr' => 'Laravel', 'en' => 'Laravel']], ['skill_category_id' => $devCategory->id, 'level' => 'Avancé', 'percentage' => 88, 'years_experience' => 4, 'sort_order' => 3, 'published' => true]);

        Experience::create([
            'profile_id' => $profile->id,
            'company' => 'Cabinet de conseil IT',
            'position' => 'Consultant ERP / Sage X3',
            'location' => 'Ouagadougou',
            'contract_type' => 'CDI',
            'start_date' => '2024-07-11',
            'is_current' => true,
            'description' => ['fr' => ['Accompagnement des entreprises dans la mise en place et l’optimisation de leurs processus ERP'], 'en' => ['Support for companies in ERP implementation and optimization']],
            'responsibilities' => ['fr' => ['Analyse des besoins', 'Configuration Sage X3', 'Accompagnement utilisateurs'], 'en' => ['Needs analysis', 'Sage X3 configuration', 'User support']],
            'achievements' => ['fr' => ['Réduction des délais de traitement', 'Sécurisation des flux de données'], 'en' => ['Reduced processing time', 'Hardened data flows']],
            'technologies' => ['Sage X3', 'SQL', 'Laravel'],
            'sort_order' => 1,
            'published' => true,
        ]);

        Certification::create([
            'title' => ['fr' => 'Certification cybersécurité', 'en' => 'Cybersecurity certification'],
            'issuer' => 'Centre de formation professionnel',
            'issue_date' => '2024-01-15',
            'description' => ['fr' => 'Certification en cybersécurité et bonnes pratiques SI', 'en' => 'Certification in cybersecurity and information security best practices'],
            'sort_order' => 1,
            'published' => true,
        ]);

        Service::create(['title' => ['fr' => 'Consulting Sage X3', 'en' => 'Sage X3 consulting'], 'description' => ['fr' => 'Conseil et accompagnement pour les projets ERP.', 'en' => 'Advice and support for ERP projects.'], 'icon' => '⚙️', 'sort_order' => 1, 'published' => true]);
        Service::create(['title' => ['fr' => 'Cybersécurité', 'en' => 'Cybersecurity'], 'description' => ['fr' => 'Audit et hardening des systèmes d’information.', 'en' => 'Audit and hardening of information systems.'], 'icon' => '🔐', 'sort_order' => 2, 'published' => true]);
        Service::create(['title' => ['fr' => 'Développement web', 'en' => 'Web development'], 'description' => ['fr' => 'Applications web modernes, évolutives et sécurisées.', 'en' => 'Modern, scalable, secure web applications.'], 'icon' => '💻', 'sort_order' => 3, 'published' => true]);

        $projectCategory = ProjectCategory::firstOrCreate(['slug' => 'erp'], ['name' => ['fr' => 'ERP', 'en' => 'ERP'], 'sort_order' => 1, 'published' => true]);
        Project::updateOrCreate(
            ['slug' => 'modernisation-erp'],
            [
                'project_category_id' => $projectCategory->id,
                'title' => ['fr' => 'Modernisation ERP', 'en' => 'ERP modernization'],
                'short_description' => ['fr' => 'Projet de modernisation des processus ERP.', 'en' => 'ERP process modernization project.'],
                'description' => ['fr' => 'Mise en place d’un plan de transformation ERP orienté processus.', 'en' => 'Implementation of a business-oriented ERP transformation plan.'],
                'project_date' => '2024-06-01',
                'client' => 'Entreprise locale',
                'role' => 'Consultant',
                'technologies' => ['Sage X3', 'SQL', 'Laravel'],
                'features' => ['fr' => ['Analyse des flux', 'Mise en conformité', 'Accompagnement'], 'en' => ['Process analysis', 'Compliance', 'Support']],
                'results' => ['fr' => ['Amélioration de la productivité', 'Réduction des erreurs'], 'en' => ['Increased productivity', 'Reduced errors']],
                'status' => 'published',
                'project_type' => 'professional',
                'sort_order' => 1,
                'published' => true,
            ]
        );

        Technology::create(['name' => ['fr' => 'Laravel', 'en' => 'Laravel'], 'category' => 'Framework', 'icon' => 'laravel', 'sort_order' => 1, 'published' => true]);
        Technology::create(['name' => ['fr' => 'Sage X3', 'en' => 'Sage X3'], 'category' => 'ERP', 'icon' => 'erp', 'sort_order' => 2, 'published' => true]);
        Technology::create(['name' => ['fr' => 'MySQL', 'en' => 'MySQL'], 'category' => 'Base de données', 'icon' => 'mysql', 'sort_order' => 3, 'published' => true]);

        Language::create(['name' => ['fr' => 'Français', 'en' => 'French'], 'locale' => 'fr', 'level' => 'Langue maternelle', 'sort_order' => 1, 'published' => true]);
        Language::create(['name' => ['fr' => 'Anglais', 'en' => 'English'], 'locale' => 'en', 'level' => 'Courant', 'sort_order' => 2, 'published' => true]);

        Testimonial::create(['author' => 'Client', 'role' => 'Directeur', 'company' => 'Entreprise', 'content' => ['fr' => 'Une collaboration claire, professionnelle et orientée résultats.', 'en' => 'A clear, professional and results-oriented collaboration.'], 'rating' => 5, 'sort_order' => 1, 'published' => true]);

        $articleCategory = ArticleCategory::firstOrCreate(['slug' => 'informatique'], ['name' => ['fr' => 'Informatique', 'en' => 'IT'], 'sort_order' => 1, 'published' => true]);
        Article::updateOrCreate(
            ['slug' => 'securite-et-cybersecurite-pme'],
            [
                'article_category_id' => $articleCategory->id,
                'title' => ['fr' => 'Sécurité et cybersécurité pour les PME', 'en' => 'Security and cybersecurity for SMEs'],
                'excerpt' => ['fr' => 'Les bonnes pratiques pour sécuriser les systèmes et les données.', 'en' => 'Best practices to secure systems and data.'],
                'content' => ['fr' => 'Contenu d’article de démonstration...', 'en' => 'Demo article content...'],
                'author_name' => 'Lino Kaboré',
                'published_at' => now(),
                'status' => 'published',
                'sort_order' => 1,
                'featured' => true,
            ]
        );
    }
}
