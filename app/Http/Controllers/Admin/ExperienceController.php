<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExperienceController extends Controller
{
    public function index()
    {
        $profile = Profile::query()->where('user_id', auth()->id())->first();
        $experiences = Experience::query()
            ->where('profile_id', $profile?->id ?? 0)
            ->orderBy('sort_order')
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('admin.experiences.index', compact('experiences', 'profile'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateExperience($request);
        $profile = Profile::query()->where('user_id', $request->user()->id)->first();

        if (! $profile) {
            return redirect()->route('admin.profile.edit')
                ->with('error', 'Créez votre profil avant d’ajouter une expérience.');
        }

        $profile->experiences()->create($this->experienceData($validated, $request));

        return redirect()->route('admin.experiences.index')->with('success', 'Expérience ajoutée avec succès.');
    }

    public function edit(Experience $experience)
    {
        $this->authorizeExperience($experience);

        return view('admin.experiences.edit', compact('experience'));
    }

    public function update(Request $request, Experience $experience)
    {
        $this->authorizeExperience($experience);
        $validated = $this->validateExperience($request);
        $experience->update($this->experienceData($validated, $request, $experience));

        return redirect()->route('admin.experiences.index')->with('success', 'Expérience mise à jour.');
    }

    public function destroy(Experience $experience)
    {
        $this->authorizeExperience($experience);
        if ($experience->image_path) {
            Storage::disk('public')->delete($experience->image_path);
        }
        $experience->delete();

        return redirect()->route('admin.experiences.index')->with('success', 'Expérience supprimée.');
    }

    private function validateExperience(Request $request): array
    {
        return $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'company_en' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'position_en' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'responsibilities_fr' => ['nullable', 'string'],
            'responsibilities_en' => ['nullable', 'string'],
            'achievements_fr' => ['nullable', 'string'],
            'achievements_en' => ['nullable', 'string'],
            'technologies' => ['nullable', 'string'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'website' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);
    }

    private function experienceData(array $validated, Request $request, ?Experience $experience = null): array
    {
        $isCurrent = $request->boolean('is_current');
        $imagePath = $experience?->image_path;
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('experiences', 'public');
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

        return [
            'company' => $validated['company'],
            'company_en' => $validated['company_en'] ?? null,
            'position' => $validated['position'],
            'position_en' => $validated['position_en'] ?? null,
            'location' => $validated['location'] ?? null,
            'contract_type' => $validated['contract_type'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $isCurrent ? null : ($validated['end_date'] ?? null),
            'is_current' => $isCurrent,
            'description' => [
                'fr' => $validated['description_fr'] ?? null,
                'en' => $validated['description_en'] ?? $validated['description_fr'] ?? null,
            ],
            'responsibilities' => [
                'fr' => $this->lines($validated['responsibilities_fr'] ?? ''),
                'en' => $this->lines($validated['responsibilities_en'] ?? $validated['responsibilities_fr'] ?? ''),
            ],
            'achievements' => [
                'fr' => $this->lines($validated['achievements_fr'] ?? ''),
                'en' => $this->lines($validated['achievements_en'] ?? $validated['achievements_fr'] ?? ''),
            ],
            'technologies' => $this->lines($validated['technologies'] ?? ''),
            'logo_path' => $validated['logo_path'] ?? null,
            'image_path' => $imagePath,
            'website' => $validated['website'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'published' => $request->boolean('published'),
        ];
    }

    private function lines(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: []), fn (string $line) => $line !== ''));
    }

    private function authorizeExperience(Experience $experience): void
    {
        abort_unless($experience->profile()->where('user_id', auth()->id())->exists(), 404);
    }
}
