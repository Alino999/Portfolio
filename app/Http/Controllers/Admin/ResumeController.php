<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Media;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProfessionalReference;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\Service;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Services\ResumeContentBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;

class ResumeController extends Controller
{
    private const SECTIONS = [
        'experience' => 'Expériences',
        'education' => 'Formation',
        'skills' => 'Compétences',
        'projects' => 'Projets',
        'certifications' => 'Certifications',
        'languages' => 'Langues',
        'services' => 'Services',
        'technologies' => 'Technologies',
        'references' => 'Références',
        'social' => 'Réseaux sociaux',
        'achievements' => 'Réalisations',
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
        $profile = Profile::query()->where('user_id', auth()->id())->first();
        $templates = ResumeTemplate::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order')->orderBy('name')->get();
        $selectedTemplateId = $templates->firstWhere('id', (int) $request->query('template'))?->id
            ?? $templates->firstWhere('is_default', true)?->id
            ?? $templates->first()?->id;
        $resumesQuery = Resume::query()->with('template');
        if ($profile) {
            $resumesQuery->where('profile_id', $profile->id);
        } else {
            $resumesQuery->whereRaw('1 = 0');
        }
        $resumes = $resumesQuery->latest()->paginate(10);
        $sections = self::SECTIONS;
        $contentOptions = $this->contentOptions($profile);
        $palettes = self::PALETTES;
        $photoMedia = $this->photoMedia();

        return view('admin.resumes.index', compact('profile', 'templates', 'resumes', 'sections', 'selectedTemplateId', 'contentOptions', 'palettes', 'photoMedia'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateResume($request);

        $profile = Profile::query()->where('user_id', $request->user()->id)->first();

        if (! $profile) {
            return redirect()->route('admin.profile.edit')->with('error', 'Créez votre profil avant de générer un CV.');
        }

        $template = ResumeTemplate::query()->findOrFail($validated['resume_template_id']);
        $contentSelection = ($request->boolean('content_selection_present') || array_key_exists('content_selection', $validated))
            ? $this->normalizeSelection($validated['content_selection'] ?? [], $profile)
            : null;
        Resume::query()->create([
            'profile_id' => $profile->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(8)),
            'locale' => $validated['locale'],
            'resume_template_id' => $validated['resume_template_id'],
            'length' => $validated['length'],
            'format' => $validated['format'],
            'sections' => $validated['sections'] ?? [],
            'content_selection' => $contentSelection,
            'customization' => $this->customization($validated['customization'] ?? [], $template),
            'target_role' => $validated['target_role'] ?? null,
            'target_market' => $validated['target_market'] ?? null,
            'summary_override' => $validated['summary_override'] ?? null,
            'is_primary' => false,
            'published' => true,
        ]);

        return redirect()->route('admin.resumes.index')->with('success', 'CV créé. Vous pouvez maintenant le télécharger.');
    }

    public function show(Request $request, Resume $resume, ResumeContentBuilder $contentBuilder)
    {
        $this->authorizeResume($resume);
        $resume->load(['profile', 'template']);
        $data = $contentBuilder->build($resume);

        return view('admin.resumes.pdf', [
            'resume' => $resume,
            'profile' => $data['profile'],
            'sections' => $data['sections'],
            'customization' => $resume->customization ?? [],
            'screenActions' => ! $request->boolean('modal'),
        ]);
    }

    public function edit(Resume $resume)
    {
        $this->authorizeResume($resume);

        $templates = ResumeTemplate::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $resume->resume_template_id))
            ->orderByDesc('is_default')->orderBy('sort_order')->orderBy('name')->get();
        $sections = self::SECTIONS;
        $contentOptions = $this->contentOptions($resume->profile);
        $palettes = self::PALETTES;
        $photoMedia = $this->photoMedia();

        return view('admin.resumes.edit', compact('resume', 'templates', 'sections', 'contentOptions', 'palettes', 'photoMedia'));
    }

    public function editModal(Resume $resume)
    {
        $this->authorizeResume($resume);

        $templates = ResumeTemplate::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $resume->resume_template_id))
            ->orderByDesc('is_default')->orderBy('sort_order')->orderBy('name')->get();
        $sections = self::SECTIONS;
        $contentOptions = $this->contentOptions($resume->profile);
        $palettes = self::PALETTES;
        $photoMedia = $this->photoMedia();

        return view('admin.resumes._edit-form', compact('resume', 'templates', 'sections', 'contentOptions', 'palettes', 'photoMedia'));
    }

    public function update(Request $request, Resume $resume)
    {
        $this->authorizeResume($resume);
        $validated = $this->validateResume($request, $resume);
        $updates = [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(8)),
            'locale' => $validated['locale'],
            'resume_template_id' => $validated['resume_template_id'],
            'length' => $validated['length'],
            'format' => $validated['format'],
            'sections' => $validated['sections'] ?? [],
        ];
        if ($request->boolean('content_selection_present') || array_key_exists('content_selection', $validated)) {
            $updates['content_selection'] = $this->normalizeSelection($validated['content_selection'] ?? [], $resume->profile);
        }
        if (array_key_exists('customization', $validated)) {
            $template = ResumeTemplate::query()->findOrFail($validated['resume_template_id']);
            $updates['customization'] = $this->customization($validated['customization'] ?? [], $template);
        }
        foreach (['target_role', 'target_market', 'summary_override'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updates[$field] = $validated[$field];
            }
        }
        $resume->update($updates);

        if ($request->expectsJson()) {
            $resume->load('template');

            return response()->json([
                'message' => 'CV mis à jour.',
                'resume' => [
                    'id' => $resume->id,
                    'title' => $resume->title,
                    'locale' => $resume->locale,
                    'format' => $resume->format,
                    'template' => $resume->template?->name,
                ],
            ]);
        }

        return redirect()->route('admin.resumes.index')->with('success', 'CV mis à jour.');
    }

    public function destroy(Resume $resume)
    {
        $this->authorizeResume($resume);
        $resume->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'CV supprimé.']);
        }

        return redirect()->route('admin.resumes.index')->with('success', 'CV supprimé.');
    }

    public function download(Resume $resume, ResumeContentBuilder $contentBuilder)
    {
        $this->authorizeResume($resume);

        $resume->load(['profile', 'template']);
        $data = $contentBuilder->build($resume);
        $filename = $resume->slug.'.'.$resume->format;

        if ($resume->format === 'pdf') {
            return Pdf::loadView('admin.resumes.pdf', [
                'resume' => $resume,
                'profile' => $data['profile'],
                'sections' => $data['sections'],
                'customization' => $resume->customization ?? [],
            ])->setPaper('a4')->download($filename);
        }

        return $this->downloadDocx($resume, $data, $filename);
    }

    private function validateResume(Request $request, ?Resume $resume = null): array
    {
        $selectedTemplate = ResumeTemplate::query()->find($request->input('resume_template_id'));
        $availableLocales = $selectedTemplate?->languages ?: ['fr', 'en'];
        $availableFormats = $selectedTemplate?->available_formats ?: ['pdf', 'docx'];

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'locale' => ['required', Rule::in($availableLocales)],
            'resume_template_id' => [
                'required',
                Rule::exists('resume_templates', 'id')->where(function ($query) use ($resume) {
                    $query->where('is_active', true);
                    if ($resume) {
                        $query->orWhere('id', $resume->resume_template_id);
                    }
                }),
            ],
            'length' => ['required', 'in:one_page,two_pages,full'],
            'format' => ['required', Rule::in($availableFormats)],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string', 'in:'.implode(',', array_keys(self::SECTIONS))],
            'content_selection_present' => ['nullable', 'boolean'],
            'content_selection' => ['nullable', 'array:experiences,educations,skills,projects,certifications,languages,technologies,services,social_links,references'],
            'content_selection.experiences' => ['nullable', 'array'],
            'content_selection.experiences.*' => ['integer', 'min:1'],
            'content_selection.educations' => ['nullable', 'array'],
            'content_selection.educations.*' => ['integer', 'min:1'],
            'content_selection.skills' => ['nullable', 'array'],
            'content_selection.skills.*' => ['integer', 'min:1'],
            'content_selection.projects' => ['nullable', 'array'],
            'content_selection.projects.*' => ['integer', 'min:1'],
            'content_selection.certifications' => ['nullable', 'array'],
            'content_selection.certifications.*' => ['integer', 'min:1'],
            'content_selection.languages' => ['nullable', 'array'],
            'content_selection.languages.*' => ['integer', 'min:1'],
            'content_selection.technologies' => ['nullable', 'array'],
            'content_selection.technologies.*' => ['integer', 'min:1'],
            'content_selection.services' => ['nullable', 'array'],
            'content_selection.services.*' => ['integer', 'min:1'],
            'content_selection.social_links' => ['nullable', 'array'],
            'content_selection.social_links.*' => ['integer', 'min:1'],
            'content_selection.references' => ['nullable', 'array'],
            'content_selection.references.*' => ['integer', 'min:1'],
            'customization' => ['nullable', 'array'],
            'customization.photo_media_id' => [
                'nullable',
                'integer',
                Rule::exists('media', 'id')->where(fn ($query) => $query
                    ->where('category', 'homepage_slider')
                    ->where('is_public', true)
                    ->where('mime_type', 'image/jpeg')),
            ],
            'customization.palette' => ['nullable', Rule::in([...array_keys(self::PALETTES), 'custom'])],
            'customization.use_custom_colors' => ['nullable', 'boolean'],
            'customization.primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'customization.secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'customization.font' => ['nullable', 'in:sans,serif,mono,Arial,Calibri,Georgia,Aptos,DejaVu Sans,Inter'],
            'customization.font_size' => ['nullable', 'integer', 'min:8', 'max:14'],
            'customization.include_photo' => ['nullable', 'boolean'],
            'customization.display_skills' => ['nullable', 'boolean'],
            'customization.display_projects' => ['nullable', 'boolean'],
            'customization.display_certifications' => ['nullable', 'boolean'],
            'customization.display_references' => ['nullable', 'boolean'],
            'customization.display_social' => ['nullable', 'boolean'],
            'target_role' => ['nullable', 'string', 'max:160'],
            'target_market' => ['nullable', 'in:burkina_faso,africa_fr,africa_en,europe,canada,international'],
            'summary_override' => ['nullable', 'string', 'max:3000'],
        ]);
    }

    private function contentOptions(?Profile $profile): array
    {
        $references = collect();
        if ($profile && Schema::hasTable('professional_references')) {
            $references = $profile->professionalReferences()->where('published', true)->orderBy('sort_order')->get();
        }

        return [
            'experiences' => $profile?->experiences()->where('published', true)->orderBy('sort_order')->get() ?? collect(),
            'educations' => $profile ? Education::query()->where('profile_id', $profile->id)->where('published', true)->orderBy('sort_order')->get() : collect(),
            'skills' => Skill::query()->where('published', true)->orderBy('sort_order')->get(),
            'projects' => Project::query()->where('published', true)->orderBy('sort_order')->get(),
            'certifications' => Certification::query()->where('published', true)->orderBy('sort_order')->get(),
            'languages' => Language::query()->where('published', true)->orderBy('sort_order')->get(),
            'technologies' => Technology::query()->where('published', true)->orderBy('sort_order')->get(),
            'services' => Service::query()->where('published', true)->orderBy('sort_order')->get(),
            'social_links' => $profile?->socialLinks()->where('is_active', true)->orderBy('sort_order')->get() ?? collect(),
            'references' => $references,
        ];
    }

    private function photoMedia()
    {
        return Media::query()
            ->where('category', 'homepage_slider')
            ->where('is_public', true)
            ->where('mime_type', 'image/jpeg')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function normalizeSelection(array $selection, Profile $profile): array
    {
        $queries = [
            'experiences' => $profile->experiences()->where('published', true),
            'educations' => Education::query()->where('profile_id', $profile->id)->where('published', true),
            'skills' => Skill::query()->where('published', true),
            'projects' => Project::query()->where('published', true),
            'certifications' => Certification::query()->where('published', true),
            'languages' => Language::query()->where('published', true),
            'technologies' => Technology::query()->where('published', true),
            'services' => Service::query()->where('published', true),
            'social_links' => $profile->socialLinks()->where('is_active', true),
            'references' => Schema::hasTable('professional_references') ? $profile->professionalReferences()->where('published', true) : null,
        ];

        $normalized = [];
        foreach ($queries as $type => $query) {
            if ($query === null) {
                $normalized[$type] = [];
                continue;
            }

            $requestedIds = array_values(array_unique(array_map('intval', $selection[$type] ?? [])));
            $normalized[$type] = $requestedIds
                ? $query->whereIn('id', $requestedIds)->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [];
        }

        return $normalized;
    }

    private function customization(array $values, ResumeTemplate $template): array
    {
        $fallbackPalette = match ($template->layout) {
            'creative', 'creative-professional' => ['primary' => '#264653', 'secondary' => '#E76F51'],
            'editorial-nordic' => ['primary' => '#263F39', 'secondary' => '#B88755'],
            'portfolio-showcase' => ['primary' => '#1E4055', 'secondary' => '#D97961'],
            'bold-header' => ['primary' => '#1F3A5F', 'secondary' => '#3E837D'],
            'graduate-focus' => ['primary' => '#2E5363', 'secondary' => '#B18B5E'],
            'portfolio-sidebar' => ['primary' => '#303734', 'secondary' => '#7AA693'],
            'warm-academic' => ['primary' => '#624B45', 'secondary' => '#C77990'],
            'elegant', 'academic', 'executive-premium' => ['primary' => '#512D3A', 'secondary' => '#A67736'],
            'tech' => ['primary' => '#132A3A', 'secondary' => '#16A6A1'],
            'cybersecurity' => ['primary' => '#162B3D', 'secondary' => '#2A9D8F'],
            default => self::PALETTES['blue_professional'],
        };
        $templatePalette = $template->available_colors[0] ?? $fallbackPalette;
        $layoutFont = match ($template->layout) {
            'academic', 'consultant', 'executive-premium', 'elegant', 'editorial-nordic', 'graduate-focus', 'warm-academic' => 'serif',
            'tech' => 'mono',
            'creative', 'creative-professional', 'portfolio-showcase' => 'Aptos',
            default => 'sans',
        };
        $paletteKey = $values['palette'] ?? 'template';
        $palette = self::PALETTES[$paletteKey] ?? $templatePalette;
        $customization = array_merge([
            'palette' => $paletteKey,
            'use_custom_colors' => false,
            'primary_color' => $palette['primary'] ?? '#17324D',
            'secondary_color' => $palette['secondary'] ?? '#0F766E',
            'font' => $layoutFont,
            'font_size' => 10,
            'include_photo' => $template->supportsPhoto(),
            'display_skills' => true,
            'display_projects' => true,
            'display_certifications' => true,
            'display_references' => true,
            'display_social' => true,
        ], $values);

        foreach (['include_photo', 'display_skills', 'display_projects', 'display_certifications', 'display_references', 'display_social'] as $toggle) {
            if ($values) {
                $customization[$toggle] = (bool) ($values[$toggle] ?? false);
            }
        }
        if (! $template->supportsPhoto()) {
            $customization['include_photo'] = false;
        }
        $customization['use_custom_colors'] = (bool) ($values['use_custom_colors'] ?? false);
        if (! $customization['use_custom_colors']) {
            $customization['primary_color'] = $palette['primary'] ?? '#17324D';
            $customization['secondary_color'] = $palette['secondary'] ?? '#0F766E';
        }

        return $customization;
    }

    private function authorizeResume(Resume $resume): void
    {
        abort_unless($resume->profile()->where('user_id', auth()->id())->exists(), 404);
    }

    private function docxColor(mixed $color, string $fallback): string
    {
        return is_string($color) && preg_match('/\A#[0-9A-Fa-f]{6}\z/', $color)
            ? substr($color, 1)
            : $fallback;
    }

    private function downloadDocx(Resume $resume, array $data, string $filename)
    {
        Settings::setOutputEscapingEnabled(true);

        $document = new PhpWord;
        $theme = match ($resume->template?->layout) {
            'modern', 'modern-executive', 'corporate', 'international', 'ats-clean', 'bold-header' => ['font' => 'Calibri', 'heading' => '17324D', 'accent' => '0F766E'],
            'portfolio-sidebar' => ['font' => 'Arial', 'heading' => '303734', 'accent' => '7AA693'],
            'warm-academic' => ['font' => 'Georgia', 'heading' => '624B45', 'accent' => 'C77990'],
            'elegant', 'academic', 'executive-premium' => ['font' => 'Georgia', 'heading' => '4B2333', 'accent' => 'A67736'],
            'editorial-nordic' => ['font' => 'Georgia', 'heading' => '263F39', 'accent' => 'B88755'],
            'graduate-focus' => ['font' => 'Georgia', 'heading' => '2E5363', 'accent' => 'B18B5E'],
            'creative', 'creative-professional' => ['font' => 'Aptos', 'heading' => '264653', 'accent' => 'E76F51'],
            'portfolio-showcase' => ['font' => 'Aptos', 'heading' => '1E4055', 'accent' => 'D97961'],
            'tech' => ['font' => 'Calibri', 'heading' => '132A3A', 'accent' => '16A6A1'],
            'cybersecurity' => ['font' => 'Arial', 'heading' => '162B3D', 'accent' => '2A9D8F'],
            'consultant' => ['font' => 'Georgia', 'heading' => '17324D', 'accent' => 'B58A4A'],
            'minimal' => ['font' => 'Arial', 'heading' => '202124', 'accent' => '6B7280'],
            default => ['font' => 'Arial', 'heading' => '17324D', 'accent' => '0F766E'],
        };
        $customization = $resume->customization ?? [];
        $theme['font'] = match ($customization['font'] ?? null) {
            'sans' => 'Arial',
            'serif' => 'Georgia',
            'mono' => 'Courier New',
            'Arial', 'Calibri', 'Georgia', 'Aptos', 'Inter' => $customization['font'],
            default => $theme['font'],
        };
        $theme['heading'] = $this->docxColor($customization['primary_color'] ?? null, $theme['heading']);
        $theme['accent'] = $this->docxColor($customization['secondary_color'] ?? null, $theme['accent']);
        $fontSize = max(8, min(14, (int) ($customization['font_size'] ?? 10)));
        $document->setDefaultFontName($theme['font']);
        $document->addTitleStyle(1, [
            'name' => $theme['font'],
            'size' => $fontSize + 16,
            'bold' => true,
            'color' => $theme['heading'],
        ]);
        $document->addTitleStyle(2, [
            'name' => $theme['font'],
            'size' => $fontSize + 5,
            'bold' => true,
            'color' => $theme['accent'],
        ]);
        $document->getDocInfo()->setTitle($resume->title);
        $section = $document->addSection();
        $avatarPath = $data['profile']['avatar_path'] ?? null;
        $contact = array_filter([
            $data['profile']['email'],
            $data['profile']['phone'],
            $data['profile']['location'],
            $data['profile']['website'],
        ]);
        $sidebarKeys = $resume->template?->layout === 'portfolio-sidebar'
            ? ['skills', 'languages', 'certifications', 'technologies']
            : ['education', 'languages'];
        $isSplitLayout = in_array($resume->template?->layout, ['portfolio-sidebar', 'warm-academic'], true);
        $sidebarSections = array_values(array_filter($data['sections'], fn ($item) => in_array($item['key'], $sidebarKeys, true)));
        $mainSections = array_values(array_filter($data['sections'], fn ($item) => ! in_array($item['key'], $sidebarKeys, true)));

        $appendSections = function ($container, array $resumeSections, bool $inSidebar = false) use ($fontSize, $theme): void {
            foreach ($resumeSections as $resumeSection) {
                if ($inSidebar) {
                    $container->addText($resumeSection['title'], ['bold' => true, 'size' => $fontSize + 2, 'color' => 'FFFFFF'], ['spaceBefore' => 180, 'spaceAfter' => 100]);
                } else {
                    $container->addTitle($resumeSection['title'], 2);
                }
                foreach ($resumeSection['items'] as $item) {
                    $container->addText($item['title'], ['bold' => true, 'color' => $inSidebar ? 'FFFFFF' : $theme['heading']]);
                    if ($item['subtitle'] || $item['meta']) {
                        $container->addText(implode(' | ', array_filter([$item['subtitle'], $item['meta']])), ['color' => $inSidebar ? 'E4ECE8' : '526271', 'size' => $fontSize - 1]);
                    }
                    if ($item['description']) {
                        $container->addText($item['description'], ['color' => $inSidebar ? 'F5F7F5' : '243342']);
                    }
                    foreach ($item['items'] as $line) {
                        $container->addListItem($line, 0, ['color' => $inSidebar ? 'F5F7F5' : '243342'], 'bullet');
                    }
                }
            }
        };

        $appendHeader = function ($container, bool $includeContact = true, bool $includeSummary = true) use ($data, $resume, $fontSize, $theme, $avatarPath): void {
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                $container->addImage(Storage::disk('public')->path($avatarPath), ['width' => 78, 'height' => 92]);
            }
            $container->addTitle($data['profile']['name'] ?: $resume->title, 1);
            if ($data['profile']['title']) {
                $container->addText($data['profile']['title'], ['bold' => true, 'size' => $fontSize + 4, 'color' => $theme['accent']]);
            }
            if ($includeContact) {
                $contact = array_filter([$data['profile']['email'], $data['profile']['phone'], $data['profile']['location'], $data['profile']['website']]);
                if ($contact) $container->addText(implode(' | ', $contact));
            }
            if ($includeSummary && $data['profile']['summary']) $container->addText($data['profile']['summary']);
        };

        if ($isSplitLayout) {
            $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 180]);
            $row = $table->addRow();
            $sidebarColor = $resume->template?->layout === 'portfolio-sidebar' ? '303734' : 'F5ECE2';
            $sidebar = $row->addCell(2900, ['bgColor' => $sidebarColor, 'valign' => 'top']);
            $main = $row->addCell(6100, ['valign' => 'top']);
            if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
                $sidebar->addImage(Storage::disk('public')->path($avatarPath), ['width' => 92, 'height' => 112]);
            }
            $sidebar->addText('Contact', ['bold' => true, 'size' => $fontSize + 2, 'color' => $resume->template?->layout === 'portfolio-sidebar' ? 'FFFFFF' : $theme['heading']]);
            foreach ($contact as $contactItem) {
                $sidebar->addText($contactItem, ['size' => $fontSize - 1, 'color' => $resume->template?->layout === 'portfolio-sidebar' ? 'E4ECE8' : '514743']);
            }
            if ($resume->template?->layout === 'portfolio-sidebar' && $data['profile']['summary']) {
                $sidebar->addText('Profil', ['bold' => true, 'size' => $fontSize + 2, 'color' => 'FFFFFF'], ['spaceBefore' => 180]);
                $sidebar->addText($data['profile']['summary'], ['size' => $fontSize - 1, 'color' => 'F5F7F5']);
            }
            $appendSections($sidebar, $sidebarSections, true);
            $appendHeader($main, false, $resume->template?->layout !== 'warm-academic');
            if ($resume->template?->layout === 'warm-academic' && $data['profile']['summary']) {
                $main->addText('Profil', ['bold' => true, 'size' => $fontSize + 2, 'color' => $theme['accent']], ['spaceBefore' => 160]);
                $main->addText($data['profile']['summary']);
            }
            $appendSections($main, $mainSections);
        } else {
            $appendHeader($section);
            $appendSections($section, $data['sections']);
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'resume_');
        IOFactory::createWriter($document, 'Word2007')->save($temporaryFile);

        return response()->download($temporaryFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
