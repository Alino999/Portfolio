<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Language;
use App\Models\ProfessionalReference;
use App\Models\Profile;
use App\Models\Service;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Support\LocaleHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;

class PortfolioEntryController extends Controller
{
    public function index(string $type)
    {
        $definition = $this->definition($type);
        $query = $definition['model']::query()->orderBy('sort_order');

        if ($definition['profile_scoped']) {
            $profile = Profile::query()->where('user_id', auth()->id())->first();
            $query->where('profile_id', $profile?->id ?? 0);
        }

        $entries = $query->paginate(10)->withQueryString();
        $entryLabels = $entries->getCollection()->mapWithKeys(fn (Model $entry) => [$entry->id => $this->entryLabel($type, $entry)]);

        return view('admin.portfolio-content.index', compact('type', 'definition', 'entries', 'entryLabels'));
    }

    public function export(string $type)
    {
        $definition = $this->definition($type);
        $query = $definition['model']::query()->orderBy('sort_order')->orderBy('id');

        if ($definition['profile_scoped']) {
            $profileId = Profile::query()->where('user_id', auth()->id())->value('id');
            $query->where('profile_id', $profileId ?? 0);
        }

        $entries = $query->get()->map(function (Model $entry): array {
            $data = $entry->attributesToArray();
            unset($data['id'], $data['profile_id'], $data['created_at'], $data['updated_at']);

            return $data;
        });

        return response()->json([
            'format' => 'portfolio-content',
            'version' => 1,
            'type' => $type,
            'exported_at' => now()->toIso8601String(),
            'entries' => $entries,
        ])->header('Content-Disposition', 'attachment; filename="portfolio-'.$type.'-'.now()->format('Ymd-His').'.json"');
    }

    public function import(Request $request, string $type)
    {
        $definition = $this->definition($type);
        $validatedFile = $request->validate([
            'import_file' => ['required', 'file', 'mimes:json', 'max:20480'],
        ]);

        try {
            $payload = json_decode($validatedFile['import_file']->get(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages(['import_file' => 'Le fichier JSON est invalide.']);
        }

        if (
            ! is_array($payload)
            || ($payload['format'] ?? null) !== 'portfolio-content'
            || ($payload['version'] ?? null) !== 1
            || ($payload['type'] ?? null) !== $type
            || ! isset($payload['entries'])
            || ! is_array($payload['entries'])
            || ! array_is_list($payload['entries'])
            || count($payload['entries']) > 500
        ) {
            throw ValidationException::withMessages(['import_file' => 'Le fichier ne correspond pas à cet onglet ou son format n’est pas pris en charge.']);
        }

        $profile = $definition['profile_scoped']
            ? Profile::query()->firstOrCreate(['user_id' => $request->user()->id])
            : null;
        $importedCount = DB::transaction(function () use ($definition, $payload, $profile, $type): int {
            foreach ($payload['entries'] as $index => $record) {
                if (! is_array($record)) {
                    throw ValidationException::withMessages(['import_file' => 'Chaque entrée du fichier doit être un objet JSON.']);
                }

                $input = $this->importFormInput($type, $record, $index);
                $validated = Validator::make($input, $this->rules($type))->validate();
                $existing = $this->matchingImportedEntry($definition, $type, $validated, $profile?->id);
                $entry = $existing ?? new $definition['model']();
                $entryRequest = Request::create('/', 'POST', [
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'published' => $validated['published'] ?? true,
                ]);
                $data = $this->entryData($type, $validated, $entryRequest, $existing);

                if (! $existing && $this->hasStoredImage($record['image_path'] ?? null)) {
                    $data['image_path'] = $record['image_path'];
                }
                if ($profile) {
                    $data['profile_id'] = $profile->id;
                }

                $entry->fill($data)->save();
            }

            return count($payload['entries']);
        });

        return redirect()->route('admin.portfolio-content.index', $type)
            ->with('success', $importedCount.' élément(s) importé(s) ou mis à jour.');
    }

    public function store(Request $request, string $type)
    {
        $definition = $this->definition($type);
        $validated = $request->validate($this->rules($type));
        $data = $this->entryData($type, $validated, $request);

        if ($definition['profile_scoped']) {
            $profile = Profile::query()->firstOrCreate(['user_id' => $request->user()->id]);
            $data['profile_id'] = $profile->id;
        }

        $definition['model']::query()->create($data);

        return redirect()->route('admin.portfolio-content.index', $type)
            ->with('success', $definition['singular'].' ajouté(e).');
    }

    public function edit(string $type, int $entry)
    {
        $definition = $this->definition($type);
        $item = $this->findEntry($type, $entry, $definition);

        return view('admin.portfolio-content.edit', compact('type', 'definition', 'item'));
    }

    public function update(Request $request, string $type, int $entry)
    {
        $definition = $this->definition($type);
        $item = $this->findEntry($type, $entry, $definition);
        $validated = $request->validate($this->rules($type));

        $item->update($this->entryData($type, $validated, $request, $item));

        return redirect()->route('admin.portfolio-content.index', $type)
            ->with('success', $definition['singular'].' mis(e) à jour.');
    }

    public function destroy(string $type, int $entry)
    {
        $definition = $this->definition($type);
        $item = $this->findEntry($type, $entry, $definition);

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('admin.portfolio-content.index', $type)
            ->with('success', $definition['singular'].' supprimé(e).');
    }

    private function definition(string $type): array
    {
        $definitions = [
            'educations' => [
                'title' => 'Formations', 'singular' => 'Formation', 'model' => Education::class, 'profile_scoped' => true,
                'fields' => [
                    ['key' => 'school', 'label' => 'Établissement', 'type' => 'text', 'required' => true],
                    ['key' => 'degree', 'label' => 'Diplôme / formation', 'type' => 'text', 'required' => true],
                    ['key' => 'field', 'label' => 'Domaine', 'type' => 'text'],
                    ['key' => 'start_date', 'label' => 'Date de début', 'type' => 'date'],
                    ['key' => 'end_date', 'label' => 'Date de fin', 'type' => 'date'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'localized', 'wide' => true],
                ],
            ],
            'certifications' => [
                'title' => 'Certifications', 'singular' => 'Certification', 'model' => Certification::class, 'profile_scoped' => false,
                'fields' => [
                    ['key' => 'title', 'label' => 'Nom', 'type' => 'localized', 'required' => true],
                    ['key' => 'issuer', 'label' => 'Organisme (FR)', 'type' => 'text'],
                    ['key' => 'issuer_en', 'label' => 'Organisme (EN)', 'type' => 'text'],
                    ['key' => 'issue_date', 'label' => 'Date d’obtention', 'type' => 'date'],
                    ['key' => 'expiration_date', 'label' => 'Date d’expiration', 'type' => 'date'],
                    ['key' => 'credential_id', 'label' => 'Identifiant', 'type' => 'text'],
                    ['key' => 'url', 'label' => 'Lien du certificat', 'type' => 'url'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'localized', 'wide' => true],
                ],
            ],
            'languages' => [
                'title' => 'Langues', 'singular' => 'Langue', 'model' => Language::class, 'profile_scoped' => false,
                'fields' => [
                    ['key' => 'name', 'label' => 'Langue', 'type' => 'localized', 'required' => true],
                    ['key' => 'locale', 'label' => 'Code langue', 'type' => 'text'],
                    ['key' => 'level', 'label' => 'Niveau', 'type' => 'text'],
                ],
            ],
            'technologies' => [
                'title' => 'Technologies', 'singular' => 'Technologie', 'model' => Technology::class, 'profile_scoped' => false,
                'fields' => [
                    ['key' => 'name', 'label' => 'Nom', 'type' => 'localized', 'required' => true],
                    ['key' => 'category', 'label' => 'Catégorie', 'type' => 'text'],
                    ['key' => 'icon', 'label' => 'Icône (classe ou symbole)', 'type' => 'text'],
                ],
            ],
            'professional-references' => [
                'title' => 'Références professionnelles', 'singular' => 'Référence', 'model' => ProfessionalReference::class, 'profile_scoped' => true,
                'fields' => [
                    ['key' => 'name', 'label' => 'Nom', 'type' => 'text', 'required' => true],
                    ['key' => 'role', 'label' => 'Fonction', 'type' => 'text'],
                    ['key' => 'company', 'label' => 'Entreprise', 'type' => 'text'],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['key' => 'phone', 'label' => 'Téléphone', 'type' => 'text'],
                ],
            ],
            'social-links' => [
                'title' => 'Réseaux sociaux', 'singular' => 'Lien social', 'model' => SocialLink::class, 'profile_scoped' => true,
                'fields' => [
                    ['key' => 'platform', 'label' => 'Plateforme', 'type' => 'text', 'required' => true],
                    ['key' => 'label', 'label' => 'Libellé affiché', 'type' => 'text'],
                    ['key' => 'url', 'label' => 'URL', 'type' => 'url', 'required' => true],
                    ['key' => 'icon', 'label' => 'Icône (classe ou symbole)', 'type' => 'text'],
                ],
            ],
            'achievements' => [
                'title' => 'Réalisations', 'singular' => 'Réalisation', 'model' => Achievement::class, 'profile_scoped' => true,
                'fields' => [
                    ['key' => 'title', 'label' => 'Titre', 'type' => 'localized', 'required' => true],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'localized', 'wide' => true],
                    ['key' => 'achievement_date', 'label' => 'Date', 'type' => 'date'],
                    ['key' => 'url', 'label' => 'Lien', 'type' => 'url'],
                ],
            ],
        ];

        abort_unless(isset($definitions[$type]), 404);

        return $definitions[$type];
    }

    private function importFormInput(string $type, array $record, int $index): array
    {
        $input = [
            'sort_order' => $record['sort_order'] ?? 0,
            'published' => $record['published'] ?? $record['is_active'] ?? true,
        ];

        foreach ($this->definition($type)['fields'] as $field) {
            $key = $field['key'];
            $value = $record[$key] ?? null;
            if ($field['type'] === 'localized') {
                if (is_array($value)) {
                    $input[$key.'_fr'] = $value['fr'] ?? null;
                    $input[$key.'_en'] = $value['en'] ?? null;
                } elseif (is_string($value)) {
                    $input[$key.'_fr'] = $value;
                    $input[$key.'_en'] = $value;
                }
            } elseif (array_key_exists($key, $record)) {
                $input[$key] = $record[$key];
            }
        }

        return $input;
    }

    private function matchingImportedEntry(array $definition, string $type, array $validated, ?int $profileId): ?Model
    {
        $query = $definition['model']::query();
        if ($definition['profile_scoped']) {
            $query->where('profile_id', $profileId ?? 0);
        }

        match ($type) {
            'educations' => $query->where('school', $validated['school'])->where('degree', $validated['degree']),
            'certifications' => $query->where('title->fr', $validated['title_fr'])->where('issuer', $validated['issuer'] ?? null),
            'languages' => ! empty($validated['locale'])
                ? $query->where('locale', $validated['locale'])
                : $query->where('name->fr', $validated['name_fr']),
            'technologies' => $query->where('name->fr', $validated['name_fr'])->where('category', $validated['category'] ?? null),
            'professional-references' => $query->where('name', $validated['name'])->where('company', $validated['company'] ?? null),
            'social-links' => $query->where('platform', $validated['platform']),
            'achievements' => $query->where('title->fr', $validated['title_fr']),
        };

        return $query->first();
    }

    private function hasStoredImage(mixed $path): bool
    {
        return is_string($path)
            && $path !== ''
            && ! str_contains($path, '..')
            && ! str_starts_with($path, '/')
            && ! str_starts_with($path, '\\')
            && Storage::disk('public')->exists($path);
    }

    private function rules(string $type): array
    {
        $common = [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ];

        return match ($type) {
            'educations' => $common + [
                'school' => ['required', 'string', 'max:255'], 'degree' => ['required', 'string', 'max:255'],
                'field' => ['nullable', 'string', 'max:255'], 'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'description_fr' => ['nullable', 'string'], 'description_en' => ['nullable', 'string'],
            ],
            'certifications' => $common + [
                'title_fr' => ['required', 'string', 'max:255'], 'title_en' => ['nullable', 'string', 'max:255'],
                'issuer' => ['nullable', 'string', 'max:255'], 'issuer_en' => ['nullable', 'string', 'max:255'],
                'issue_date' => ['nullable', 'date'], 'expiration_date' => ['nullable', 'date'],
                'credential_id' => ['nullable', 'string', 'max:255'], 'url' => ['nullable', 'url', 'max:2048'],
                'description_fr' => ['nullable', 'string'], 'description_en' => ['nullable', 'string'],
            ],
            'languages' => $common + [
                'name_fr' => ['required', 'string', 'max:255'], 'name_en' => ['nullable', 'string', 'max:255'],
                'locale' => ['nullable', 'string', 'max:10'], 'level' => ['nullable', 'string', 'max:100'],
            ],
            'technologies' => $common + [
                'name_fr' => ['required', 'string', 'max:255'], 'name_en' => ['nullable', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:100'], 'icon' => ['nullable', 'string', 'max:100'],
            ],
            'professional-references' => $common + [
                'name' => ['required', 'string', 'max:255'], 'role' => ['nullable', 'string', 'max:255'],
                'company' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:100'],
            ],
            'social-links' => $common + [
                'platform' => ['required', 'string', 'max:100'], 'label' => ['nullable', 'string', 'max:255'],
                'url' => ['required', 'url', 'max:2048'], 'icon' => ['nullable', 'string', 'max:100'],
            ],
            'achievements' => $common + [
                'title_fr' => ['required', 'string', 'max:255'], 'title_en' => ['nullable', 'string', 'max:255'],
                'description_fr' => ['nullable', 'string'], 'description_en' => ['nullable', 'string'],
                'achievement_date' => ['nullable', 'date'], 'url' => ['nullable', 'url', 'max:2048'],
            ],
            default => abort(404),
        };
    }

    private function entryData(string $type, array $validated, Request $request, ?Model $entry = null): array
    {
        $imagePath = $entry?->image_path;
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('portfolio-content/'.$type, 'public');
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $newImagePath;
        } elseif ($request->boolean('remove_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }

        $data = match ($type) {
            'educations' => [
                'school' => $validated['school'], 'degree' => $validated['degree'], 'field' => $validated['field'] ?? null,
                'start_date' => $validated['start_date'] ?? null, 'end_date' => $validated['end_date'] ?? null,
                'description' => $this->localized($validated, 'description'),
            ],
            'certifications' => [
                'title' => $this->localized($validated, 'title'), 'issuer' => $validated['issuer'] ?? null,
                'issuer_en' => $validated['issuer_en'] ?? null, 'issue_date' => $validated['issue_date'] ?? null,
                'expiration_date' => $validated['expiration_date'] ?? null, 'credential_id' => $validated['credential_id'] ?? null,
                'url' => $validated['url'] ?? null, 'description' => $this->localized($validated, 'description'),
            ],
            'languages' => [
                'name' => $this->localized($validated, 'name'), 'locale' => $validated['locale'] ?? null,
                'level' => $validated['level'] ?? null,
            ],
            'technologies' => [
                'name' => $this->localized($validated, 'name'), 'category' => $validated['category'] ?? null,
                'icon' => $validated['icon'] ?? null,
            ],
            'professional-references' => [
                'name' => $validated['name'], 'role' => $validated['role'] ?? null,
                'company' => $validated['company'] ?? null, 'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ],
            'social-links' => [
                'platform' => $validated['platform'], 'label' => $validated['label'] ?? null,
                'url' => $validated['url'], 'icon' => $validated['icon'] ?? null,
                'is_active' => $request->boolean('published'),
            ],
            'achievements' => [
                'title' => $this->localized($validated, 'title'), 'description' => $this->localized($validated, 'description'),
                'achievement_date' => $validated['achievement_date'] ?? null, 'url' => $validated['url'] ?? null,
            ],
        };

        $data['image_path'] = $imagePath;
        $data['sort_order'] = $validated['sort_order'] ?? 0;
        if ($type !== 'social-links') {
            $data['published'] = $request->boolean('published');
        }

        return $data;
    }

    private function localized(array $validated, string $key): array
    {
        $fr = $validated[$key.'_fr'] ?? null;

        return [
            'fr' => $fr,
            'en' => $validated[$key.'_en'] ?? $fr,
        ];
    }

    private function findEntry(string $type, int $id, array $definition): Model
    {
        $query = $definition['model']::query();
        if ($definition['profile_scoped']) {
            $profileId = Profile::query()->where('user_id', auth()->id())->value('id');
            $query->where('profile_id', $profileId ?? 0);
        }

        return $query->findOrFail($id);
    }

    private function entryLabel(string $type, Model $entry): string
    {
        return match ($type) {
            'educations' => trim($entry->degree.' — '.$entry->school, ' —'),
            'professional-references' => $entry->name,
            'social-links' => $entry->label ?: $entry->platform,
            'languages', 'technologies' => LocaleHelper::text($entry->name),
            default => LocaleHelper::text($entry->title),
        };
    }
}