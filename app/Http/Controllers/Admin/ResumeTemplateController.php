<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\ResumeTemplateCategory;
use App\Services\ResumeContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResumeTemplateController extends Controller
{
    private const LAYOUTS = [
        'classic', 'modern', 'elegant', 'creative',
        'classic-professional', 'modern-executive', 'minimal', 'corporate',
        'international', 'ats-clean', 'tech', 'cybersecurity',
        'consultant', 'academic', 'creative-professional', 'executive-premium',
        'editorial-nordic', 'portfolio-showcase', 'bold-header', 'graduate-focus',
        'portfolio-sidebar', 'warm-academic',
    ];

    private const BLOCKS = [
        'header' => 'En-tête et coordonnées',
        'photo' => 'Photo',
        'profile' => 'Profil',
        'summary' => 'Résumé professionnel',
        'experience' => 'Expériences',
        'education' => 'Formation',
        'skills' => 'Compétences',
        'projects' => 'Projets',
        'certifications' => 'Certifications',
        'languages' => 'Langues',
        'technologies' => 'Technologies',
        'achievements' => 'Réalisations',
        'references' => 'Références',
        'social' => 'Réseaux sociaux',
    ];

    private const PALETTES = [
        'blue_professional' => ['name' => 'Bleu professionnel', 'primary' => '#17324D', 'secondary' => '#0F766E'],
        'night_blue' => ['name' => 'Bleu nuit', 'primary' => '#142B45', 'secondary' => '#42A5A0'],
        'corporate_gray' => ['name' => 'Gris corporate', 'primary' => '#39434D', 'secondary' => '#718096'],
        'monochrome' => ['name' => 'Noir et blanc', 'primary' => '#202124', 'secondary' => '#6B7280'],
        'forest_green' => ['name' => 'Vert sobre', 'primary' => '#315B4C', 'secondary' => '#7E9B8C'],
        'muted_red' => ['name' => 'Rouge sobre', 'primary' => '#492B32', 'secondary' => '#B55D5D'],
        'violet' => ['name' => 'Violet', 'primary' => '#443B5C', 'secondary' => '#A68AB5'],
    ];

    public function index(Request $request)
    {
        $templates = ResumeTemplate::query()
            ->with('category')
            ->when(! $request->boolean('inactive'), fn ($templateQuery) => $templateQuery->where('is_active', true))
            ->when($request->filled('category'), fn ($templateQuery) => $templateQuery->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $request->string('category'))))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        $categories = ResumeTemplateCategory::query()->where('is_active', true)->orderBy('sort_order')->get();
        $layouts = self::LAYOUTS;
        $blocks = self::BLOCKS;
        $palettes = self::PALETTES;

        return view('admin.resume-templates.index', compact('templates', 'categories', 'layouts', 'blocks', 'palettes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);
        $category = ResumeTemplateCategory::query()->findOrFail($validated['category_id']);

        ResumeTemplate::query()->create($this->templateData($validated, $request, $category));

        return redirect()->route('admin.resume-templates.index')->with('success', 'Modèle ajouté à la bibliothèque.');
    }

    public function edit(ResumeTemplate $resumeTemplate)
    {
        return view('admin.resume-templates.edit', $this->formData($resumeTemplate));
    }

    public function update(Request $request, ResumeTemplate $resumeTemplate)
    {
        $validated = $this->validateTemplate($request, $resumeTemplate);
        $category = ResumeTemplateCategory::query()->findOrFail($validated['category_id']);
        $data = $this->templateData($validated, $request, $category);
        unset($data['is_system']);
        $resumeTemplate->update($data);

        return redirect()->route('admin.resume-templates.index')->with('success', 'Modèle mis à jour.');
    }

    public function duplicate(ResumeTemplate $resumeTemplate)
    {
        $copy = $resumeTemplate->replicate(['is_default', 'is_system']);
        $copy->name = $resumeTemplate->name.' - Copie';
        $baseSlug = Str::slug($resumeTemplate->slug.'-copy');
        $slug = $baseSlug;
        $suffix = 2;
        while (ResumeTemplate::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }
        $copy->slug = $slug;
        $copy->is_default = false;
        $copy->is_system = false;
        $copy->sort_order = (ResumeTemplate::query()->max('sort_order') ?? 0) + 1;
        $copy->save();

        return redirect()->route('admin.resume-templates.index')->with('success', 'Modèle dupliqué.');
    }

    public function toggle(ResumeTemplate $resumeTemplate)
    {
        $resumeTemplate->update(['is_active' => ! $resumeTemplate->is_active]);

        return redirect()->route('admin.resume-templates.index')->with('success', $resumeTemplate->is_active ? 'Modèle activé.' : 'Modèle désactivé.');
    }

    public function destroy(ResumeTemplate $resumeTemplate)
    {
        if ($resumeTemplate->is_system) {
            return redirect()->route('admin.resume-templates.index')->with('error', 'Les modèles livrés avec la bibliothèque doivent être désactivés plutôt que supprimés.');
        }

        if (Resume::query()->where('resume_template_id', $resumeTemplate->id)->exists()) {
            return redirect()->route('admin.resume-templates.index')->with('error', 'Ce modèle est utilisé par un CV. Désactivez-le pour le retirer de la bibliothèque.');
        }

        $resumeTemplate->delete();

        return redirect()->route('admin.resume-templates.index')->with('success', 'Modèle supprimé.');
    }

    public function preview(Request $request, ResumeTemplate $resumeTemplate, ResumeContentBuilder $contentBuilder)
    {
        $locale = in_array($request->query('locale'), ['fr', 'en'], true) ? $request->query('locale') : 'fr';
        $length = in_array($request->query('length'), ['one_page', 'two_pages', 'full'], true) ? $request->query('length') : $resumeTemplate->recommended_pages;
        $profile = Profile::query()->where('user_id', $request->user()->id)->first();
        $isRealData = $request->query('data') === 'real' && $profile !== null;

        if ($isRealData) {
            $resume = new Resume([
                'profile_id' => $profile->id,
                'resume_template_id' => $resumeTemplate->id,
                'locale' => $locale,
                'length' => $length,
                'sections' => ['experience', 'education', 'skills', 'projects', 'certifications', 'languages'],
                'customization' => ['include_photo' => $resumeTemplate->supportsPhoto()],
            ]);
            $resume->setRelation('profile', $profile);
            $resume->setRelation('template', $resumeTemplate);
            $content = $contentBuilder->build($resume);
            $previewProfile = $content['profile'];
            $previewSections = $content['sections'];
        } else {
            $previewProfile = [
                'name' => 'Alain Guigma',
                'title' => $locale === 'fr' ? 'Consultant ERP / Sage X3' : 'ERP / Sage X3 Consultant',
                'summary' => $locale === 'fr'
                    ? 'Consultant orienté résultats, spécialisé en transformation digitale, optimisation des processus et accompagnement des équipes métiers.'
                    : 'Results-oriented consultant specialized in digital transformation, process optimization, and business team enablement.',
                'email' => 'alain@example.com',
                'phone' => '+226 70 00 00 00',
                'location' => $locale === 'fr' ? 'Ouagadougou, Burkina Faso' : 'Ouagadougou, Burkina Faso',
                'avatar_path' => $resumeTemplate->supportsPhoto() ? $profile?->avatar_path : null,
                'website' => 'alainguigma.com',
                'linkedin' => '',
                'github' => '',
            ];
            $previewSections = $this->demoSections($locale, $length);
        }

        return view('admin.resume-templates.preview', compact('resumeTemplate', 'previewProfile', 'previewSections', 'locale', 'length', 'isRealData', 'profile'));
    }

    private function demoSections(string $locale, string $length): array
    {
        $labels = $locale === 'fr'
            ? ['Expériences professionnelles', 'Compétences', 'Projets']
            : ['Professional Experience', 'Skills', 'Projects'];
        $sections = [
            [
                'key' => 'experience',
                'title' => $labels[0],
                'items' => [[
                    'title' => $locale === 'fr' ? 'Consultante en transformation numérique' : 'Digital Transformation Consultant',
                    'subtitle' => 'Cabinet Horizon',
                    'meta' => '2022 - '.($locale === 'fr' ? 'Aujourd’hui' : 'Present'),
                    'description' => $locale === 'fr' ? 'Pilotage de projets de modernisation des systèmes d’information.' : 'Led information systems modernization projects.',
                    'items' => $locale === 'fr' ? ['Coordination des parties prenantes', 'Amélioration des processus'] : ['Stakeholder coordination', 'Process improvement'],
                ]],
            ],
            [
                'key' => 'skills',
                'title' => $labels[1],
                'items' => array_map(fn ($name) => ['title' => $name, 'subtitle' => 'Avancé', 'meta' => '', 'description' => '', 'items' => []], ['Gestion de projet', 'Sage X3', 'Cybersécurité', 'Analyse métier']),
            ],
            [
                'key' => 'projects',
                'title' => $labels[2],
                'items' => [[
                    'title' => $locale === 'fr' ? 'Harmonisation des outils métier' : 'Business Tools Alignment',
                    'subtitle' => 'Projet régional',
                    'meta' => '2023',
                    'description' => $locale === 'fr' ? 'Déploiement coordonné d’un socle applicatif multi-sites.' : 'Coordinated rollout of a multi-site application platform.',
                    'items' => [],
                ]],
            ],
        ];

        return $length === 'one_page' ? array_slice($sections, 0, 2) : $sections;
    }

    private function validateTemplate(Request $request, ?ResumeTemplate $resumeTemplate = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('resume_templates', 'slug')->ignore($resumeTemplate?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['required', 'exists:resume_template_categories,id'],
            'style' => ['required', 'string', 'max:80'],
            'layout' => ['required', Rule::in(self::LAYOUTS)],
            'ats_friendly' => ['nullable', 'boolean'],
            'languages' => ['required', 'array', 'min:1'],
            'languages.*' => ['string', 'in:fr,en'],
            'recommended_pages' => ['required', 'in:one_page,two_pages,full'],
            'available_formats' => ['required', 'array', 'min:1'],
            'available_formats.*' => ['string', 'in:pdf,docx'],
            'palettes' => ['nullable', 'array'],
            'palettes.*' => ['string', Rule::in(array_keys(self::PALETTES))],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*' => ['string', Rule::in(array_keys(self::BLOCKS))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function templateData(array $validated, Request $request, ResumeTemplateCategory $category): array
    {
        $paletteKeys = $validated['palettes'] ?? ['blue_professional'];
        $colors = array_map(fn (string $key) => self::PALETTES[$key], $paletteKeys);

        return [
            'category_id' => $category->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'style' => $validated['style'],
            'layout' => $validated['layout'],
            'ats_friendly' => $request->boolean('ats_friendly'),
            'languages' => array_values($validated['languages']),
            'recommended_pages' => $validated['recommended_pages'],
            'available_formats' => array_values($validated['available_formats']),
            'available_colors' => $colors,
            'blocks' => array_values($validated['blocks']),
            'is_active' => $request->boolean('is_active'),
            'is_system' => false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }

    private function formData(?ResumeTemplate $resumeTemplate = null): array
    {
        return [
            'template' => $resumeTemplate,
            'categories' => ResumeTemplateCategory::query()->orderBy('sort_order')->get(),
            'layouts' => self::LAYOUTS,
            'blocks' => self::BLOCKS,
            'palettes' => self::PALETTES,
        ];
    }
}
