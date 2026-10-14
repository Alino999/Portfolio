<?php

namespace Database\Seeders;

use App\Models\ResumeTemplate;
use App\Models\ResumeTemplateCategory;
use Illuminate\Database\Seeder;

class AdditionalResumeTemplateSeeder extends Seeder
{
    private const CATEGORIES = [
        ['Elegant', 'elegant'],
        ['Creative', 'creatif'],
        ['Modern', 'moderne'],
        ['Academic', 'academique'],
    ];

    private const TEMPLATES = [
        [
            'name' => 'Nordic Editorial', 'slug' => 'editorial-nordic', 'category' => 'elegant',
            'style' => 'editorial', 'layout' => 'editorial-nordic', 'ats' => true, 'pages' => 'two_pages',
            'colors' => [
                ['name' => 'Sauge et cuivre', 'primary' => '#263F39', 'secondary' => '#B88755'],
                ['name' => 'Ardoise claire', 'primary' => '#34424C', 'secondary' => '#718A83'],
            ],
            'blocks' => ['header', 'summary', 'experience', 'education', 'skills', 'projects', 'languages'],
            'description' => 'Une composition editoriale aeree avec une typographie sobre et des reperes discrets.',
        ],
        [
            'name' => 'Portfolio Showcase', 'slug' => 'portfolio-showcase', 'category' => 'creatif',
            'style' => 'project-led', 'layout' => 'portfolio-showcase', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [
                ['name' => 'Encre et corail', 'primary' => '#1E4055', 'secondary' => '#D97961'],
                ['name' => 'Foret et cuivre', 'primary' => '#315B4C', 'secondary' => '#C18A58'],
            ],
            'blocks' => ['header', 'profile', 'projects', 'achievements', 'experience', 'skills', 'education'],
            'description' => 'Les projets et realisations prennent la premiere place dans une presentation expressive.',
        ],
        [
            'name' => 'Bold Header', 'slug' => 'bold-header', 'category' => 'moderne',
            'style' => 'contemporary', 'layout' => 'bold-header', 'ats' => true, 'pages' => 'one_page',
            'colors' => [
                ['name' => 'Bleu profond', 'primary' => '#1F3A5F', 'secondary' => '#3E837D'],
                ['name' => 'Graphite et jade', 'primary' => '#303A40', 'secondary' => '#578C7E'],
            ],
            'blocks' => ['header', 'summary', 'skills', 'experience', 'education', 'certifications'],
            'description' => 'Un en-tete affirme ouvre une structure lineaire et compacte, adaptee a une page.',
        ],
        [
            'name' => 'Graduate Focus', 'slug' => 'graduate-focus', 'category' => 'academique',
            'style' => 'academic-modern', 'layout' => 'graduate-focus', 'ats' => true, 'pages' => 'full',
            'colors' => [
                ['name' => 'Bleu petrole', 'primary' => '#2E5363', 'secondary' => '#B18B5E'],
                ['name' => 'Noir et sauge', 'primary' => '#30383A', 'secondary' => '#68877E'],
            ],
            'blocks' => ['header', 'profile', 'education', 'certifications', 'projects', 'experience', 'skills', 'languages'],
            'description' => 'La formation, les certifications et les travaux academiques structurent le parcours.',
        ],
        [
            'name' => 'Portfolio Sidebar', 'slug' => 'portfolio-sidebar', 'category' => 'creatif',
            'style' => 'split-sidebar', 'layout' => 'portfolio-sidebar', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [
                ['name' => 'Graphite et sauge', 'primary' => '#303734', 'secondary' => '#7AA693'],
                ['name' => 'Bleu ardoise', 'primary' => '#263B46', 'secondary' => '#79AEB0'],
            ],
            'blocks' => ['header', 'profile', 'summary', 'experience', 'education', 'skills', 'languages', 'certifications', 'projects', 'references', 'technologies'],
            'description' => 'Une colonne laterale sombre pour le portrait, le contact et les competences, avec le parcours dans la zone principale.',
        ],
        [
            'name' => 'Warm Academic', 'slug' => 'warm-academic', 'category' => 'academique',
            'style' => 'warm-two-column', 'layout' => 'warm-academic', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [
                ['name' => 'Ivoire et rose argile', 'primary' => '#624B45', 'secondary' => '#C77990'],
                ['name' => 'Sable et sauge', 'primary' => '#4E554D', 'secondary' => '#9B806A'],
            ],
            'blocks' => ['header', 'profile', 'summary', 'education', 'experience', 'skills', 'languages', 'certifications', 'projects'],
            'description' => 'Une page claire et chaleureuse qui met les formations et le profil en evidence dans deux colonnes.',
        ],
    ];

    public function run(): void
    {
        $categories = [];
        foreach (self::CATEGORIES as $sortOrder => [$name, $slug]) {
            $categories[$slug] = ResumeTemplateCategory::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $sortOrder + 1, 'is_active' => true],
            );
        }

        foreach (self::TEMPLATES as $sortOrder => $definition) {
            ResumeTemplate::query()->firstOrCreate(
                ['slug' => $definition['slug']],
                [
                    'category_id' => $categories[$definition['category']]->id,
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'layout' => $definition['layout'],
                    'style' => $definition['style'],
                    'ats_friendly' => $definition['ats'],
                    'languages' => ['fr', 'en'],
                    'recommended_pages' => $definition['pages'],
                    'available_formats' => ['pdf', 'docx'],
                    'available_colors' => $definition['colors'],
                    'blocks' => $definition['blocks'],
                    'is_active' => true,
                    'is_default' => false,
                    'is_system' => true,
                    'sort_order' => $sortOrder + 13,
                ],
            );
        }
    }
}
