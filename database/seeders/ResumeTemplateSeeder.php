<?php

namespace Database\Seeders;

use App\Models\ResumeTemplate;
use App\Models\ResumeTemplateCategory;
use Illuminate\Database\Seeder;

class ResumeTemplateSeeder extends Seeder
{
    private const CATEGORIES = [
        ['Classique', 'classique'],
        ['Moderne', 'moderne'],
        ['Minimaliste', 'minimaliste'],
        ['Élégant', 'elegant'],
        ['Professionnel', 'professionnel'],
        ['Créatif', 'creatif'],
        ['Corporate', 'corporate'],
        ['International', 'international'],
        ['ATS', 'ats'],
        ['Informatique', 'informatique'],
        ['Cybersécurité', 'cybersecurite'],
        ['Consultant', 'consultant'],
        ['Management', 'management'],
        ['Académique', 'academique'],
    ];

    private const TEMPLATES = [
        [
            'name' => 'Classic Professional', 'slug' => 'classic-professional', 'category' => 'classique',
            'style' => 'traditional', 'layout' => 'classic-professional', 'ats' => true, 'pages' => 'two_pages',
            'colors' => [['name' => 'Bleu professionnel', 'primary' => '#17324D', 'secondary' => '#0F766E'], ['name' => 'Noir et blanc', 'primary' => '#202124', 'secondary' => '#6B7280']],
            'blocks' => ['header', 'summary', 'experience', 'skills', 'education', 'certifications'],
        ],
        [
            'name' => 'Modern Executive', 'slug' => 'modern-executive', 'category' => 'management',
            'style' => 'executive', 'layout' => 'modern-executive', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [['name' => 'Bleu nuit', 'primary' => '#142B45', 'secondary' => '#42A5A0'], ['name' => 'Gris corporate', 'primary' => '#39434D', 'secondary' => '#9AA6B2']],
            'blocks' => ['header', 'profile', 'experience', 'skills', 'projects', 'education', 'certifications'],
        ],
        [
            'name' => 'Minimal', 'slug' => 'minimal', 'category' => 'minimaliste',
            'style' => 'minimal', 'layout' => 'minimal', 'ats' => true, 'pages' => 'one_page',
            'colors' => [['name' => 'Noir et blanc', 'primary' => '#202124', 'secondary' => '#777777'], ['name' => 'Vert sobre', 'primary' => '#315B4C', 'secondary' => '#7E9B8C']],
            'blocks' => ['header', 'summary', 'skills', 'experience', 'education', 'languages'],
        ],
        [
            'name' => 'Corporate', 'slug' => 'corporate', 'category' => 'corporate',
            'style' => 'corporate', 'layout' => 'corporate', 'ats' => true, 'pages' => 'two_pages',
            'colors' => [['name' => 'Gris corporate', 'primary' => '#34495E', 'secondary' => '#477A8A'], ['name' => 'Bleu professionnel', 'primary' => '#17324D', 'secondary' => '#0F766E']],
            'blocks' => ['header', 'profile', 'experience', 'education', 'skills', 'certifications', 'references'],
        ],
        [
            'name' => 'International', 'slug' => 'international', 'category' => 'international',
            'style' => 'international', 'layout' => 'international', 'ats' => true, 'pages' => 'two_pages',
            'colors' => [['name' => 'Bleu professionnel', 'primary' => '#17324D', 'secondary' => '#4B7188'], ['name' => 'Noir et blanc', 'primary' => '#202124', 'secondary' => '#777777']],
            'blocks' => ['header', 'summary', 'experience', 'skills', 'education', 'projects', 'languages'],
        ],
        [
            'name' => 'ATS Clean', 'slug' => 'ats-clean', 'category' => 'ats',
            'style' => 'linear', 'layout' => 'ats-clean', 'ats' => true, 'pages' => 'two_pages',
            'colors' => [['name' => 'Noir et blanc', 'primary' => '#171717', 'secondary' => '#525252'], ['name' => 'Bleu nuit', 'primary' => '#172A3A', 'secondary' => '#425B70']],
            'blocks' => ['header', 'summary', 'experience', 'skills', 'education', 'certifications', 'projects'],
        ],
        [
            'name' => 'Tech', 'slug' => 'tech', 'category' => 'informatique',
            'style' => 'technical', 'layout' => 'tech', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [['name' => 'Bleu nuit', 'primary' => '#132A3A', 'secondary' => '#16A6A1'], ['name' => 'Vert sobre', 'primary' => '#214E46', 'secondary' => '#72B7A3']],
            'blocks' => ['header', 'skills', 'technologies', 'experience', 'projects', 'education', 'certifications'],
        ],
        [
            'name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'category' => 'cybersecurite',
            'style' => 'security', 'layout' => 'cybersecurity', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [['name' => 'Bleu nuit', 'primary' => '#162B3D', 'secondary' => '#2A9D8F'], ['name' => 'Rouge sobre', 'primary' => '#492B32', 'secondary' => '#B55D5D']],
            'blocks' => ['header', 'summary', 'skills', 'certifications', 'experience', 'projects', 'education'],
        ],
        [
            'name' => 'Consultant', 'slug' => 'consultant', 'category' => 'consultant',
            'style' => 'editorial', 'layout' => 'consultant', 'ats' => true, 'pages' => 'two_pages',
            'colors' => [['name' => 'Bleu professionnel', 'primary' => '#17324D', 'secondary' => '#B58A4A'], ['name' => 'Gris corporate', 'primary' => '#34495E', 'secondary' => '#718096']],
            'blocks' => ['header', 'profile', 'expertise', 'experience', 'projects', 'skills', 'education'],
        ],
        [
            'name' => 'Academic', 'slug' => 'academic', 'category' => 'academique',
            'style' => 'academic', 'layout' => 'academic', 'ats' => true, 'pages' => 'full',
            'colors' => [['name' => 'Noir et blanc', 'primary' => '#202124', 'secondary' => '#666666'], ['name' => 'Bleu professionnel', 'primary' => '#17324D', 'secondary' => '#526A85']],
            'blocks' => ['header', 'profile', 'education', 'experience', 'projects', 'publications', 'skills', 'languages'],
        ],
        [
            'name' => 'Creative Professional', 'slug' => 'creative-professional', 'category' => 'creatif',
            'style' => 'creative', 'layout' => 'creative-professional', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [['name' => 'Rouge sobre', 'primary' => '#264653', 'secondary' => '#E76F51'], ['name' => 'Violet', 'primary' => '#443B5C', 'secondary' => '#A68AB5']],
            'blocks' => ['header', 'profile', 'skills', 'projects', 'experience', 'education', 'languages'],
        ],
        [
            'name' => 'Executive Premium', 'slug' => 'executive-premium', 'category' => 'management',
            'style' => 'premium', 'layout' => 'executive-premium', 'ats' => false, 'pages' => 'two_pages',
            'colors' => [['name' => 'Bleu nuit', 'primary' => '#142B45', 'secondary' => '#C2A36B'], ['name' => 'Noir et blanc', 'primary' => '#202124', 'secondary' => '#A38D68']],
            'blocks' => ['header', 'profile', 'executive-summary', 'experience', 'achievements', 'leadership', 'education'],
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
            $category = $categories[$definition['category']];
            $template = ResumeTemplate::query()->firstOrCreate(
                ['slug' => $definition['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $definition['name'],
                    'description' => $this->description($definition['name']),
                    'layout' => $definition['layout'],
                    'style' => $definition['style'],
                    'ats_friendly' => $definition['ats'],
                    'languages' => ['fr', 'en'],
                    'recommended_pages' => $definition['pages'],
                    'available_formats' => ['pdf', 'docx'],
                    'available_colors' => $definition['colors'],
                    'blocks' => $definition['blocks'],
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => $sortOrder + 1,
                ],
            );

            $template->forceFill([
                'category_id' => $template->category_id ?: $category->id,
                'style' => $template->style ?: $definition['style'],
                'ats_friendly' => $template->ats_friendly || $definition['ats'],
                'languages' => $template->languages ?: ['fr', 'en'],
                'available_formats' => $template->available_formats ?: ['pdf', 'docx'],
                'available_colors' => $template->available_colors ?: $definition['colors'],
                'blocks' => $template->blocks ?: $definition['blocks'],
                'is_system' => true,
            ])->save();
        }

        $this->completeLegacyTemplates($categories);
        $this->call(AdditionalResumeTemplateSeeder::class);
    }

    private function completeLegacyTemplates(array $categories): void
    {
        $legacy = [
            'classic' => ['classique', 'traditional', true],
            'modern' => ['moderne', 'contemporary', false],
            'elegant' => ['elegant', 'refined', false],
            'creative' => ['creatif', 'creative', false],
        ];

        foreach ($legacy as $slug => [$categorySlug, $style, $atsFriendly]) {
            $template = ResumeTemplate::query()->where('slug', $slug)->first();
            if (! $template) {
                continue;
            }

            $template->forceFill([
                'category_id' => $template->category_id ?: $categories[$categorySlug]->id,
                'style' => $template->style ?: $style,
                'ats_friendly' => $template->ats_friendly || $atsFriendly,
                'languages' => $template->languages ?: ['fr', 'en'],
                'recommended_pages' => $template->recommended_pages ?: 'two_pages',
                'available_formats' => $template->available_formats ?: ['pdf', 'docx'],
                'available_colors' => $template->available_colors ?: [],
                'blocks' => $template->blocks ?: ['header', 'profile', 'experience', 'skills', 'education'],
            ])->save();
        }
    }

    private function description(string $name): string
    {
        return match ($name) {
            'Classic Professional' => 'Une structure chronologique claire, sobre et facile à parcourir.',
            'Modern Executive' => 'Une hiérarchie contemporaine conçue pour les cadres et dirigeants.',
            'Minimal' => 'Une page épurée, centrée sur l’essentiel et la respiration visuelle.',
            'Corporate' => 'Une présentation structurée adaptée aux entreprises et cabinets.',
            'International' => 'Un format international sans données personnelles imposées.',
            'ATS Clean' => 'Une colonne linéaire, des rubriques standard et un texte sélectionnable.',
            'Tech' => 'Les compétences techniques et projets sont placés au premier plan.',
            'Cybersecurity' => 'Un parcours orienté sécurité, certifications et réalisations.',
            'Consultant' => 'Une synthèse d’expertise, de missions et de résultats clients.',
            'Academic' => 'La formation et les travaux de recherche précèdent le parcours.',
            'Creative Professional' => 'Une composition visuelle affirmée qui conserve une lecture professionnelle.',
            'Executive Premium' => 'Une mise en page éditoriale pour profils expérimentés et responsabilités élevées.',
            default => 'Modèle professionnel configurable.',
        };
    }
}
