<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::query()->with('category')->orderBy('sort_order')->paginate(10);
        $categories = SkillCategory::query()->orderBy('sort_order')->get();

        return view('admin.skills.index', compact('skills', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_category_id' => ['nullable', 'exists:skill_categories,id'],
            'name_fr' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:100'],
            'level_en' => ['nullable', 'string', 'max:100'],
            'percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);

        Skill::query()->create([
            'skill_category_id' => $validated['skill_category_id'] ?? null,
            'name' => [
                'fr' => $validated['name_fr'],
                'en' => $validated['name_en'] ?? $validated['name_fr'],
            ],
            'level' => $validated['level'] ?? null,
            'level_en' => $validated['level_en'] ?? null,
            'percentage' => $validated['percentage'] ?? null,
            'years_experience' => $validated['years_experience'] ?? 0,
            'description' => [
                'fr' => $validated['description_fr'] ?? null,
                'en' => $validated['description_en'] ?? $validated['description_fr'] ?? null,
            ],
            'icon' => $validated['icon'] ?? null,
            'image_path' => $request->file('image')?->store('skills', 'public'),
            'sort_order' => $validated['sort_order'] ?? 0,
            'published' => $request->boolean('published'),
        ]);

        return redirect()->route('admin.skills.index')->with('success', 'Compétence ajoutée avec succès.');
    }

    public function edit(Skill $skill)
    {
        $categories = SkillCategory::query()->orderBy('sort_order')->get();

        return view('admin.skills.edit', compact('skill', 'categories'));
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'skill_category_id' => ['nullable', 'exists:skill_categories,id'],
            'name_fr' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:100'],
            'level_en' => ['nullable', 'string', 'max:100'],
            'percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);

        $imagePath = $skill->image_path;
        if ($request->boolean('remove_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('skills', 'public');
        }

        $skill->update([
            'skill_category_id' => $validated['skill_category_id'] ?? null,
            'name' => [
                'fr' => $validated['name_fr'],
                'en' => $validated['name_en'] ?? $validated['name_fr'],
            ],
            'level' => $validated['level'] ?? null,
            'level_en' => $validated['level_en'] ?? null,
            'percentage' => $validated['percentage'] ?? null,
            'years_experience' => $validated['years_experience'] ?? 0,
            'description' => [
                'fr' => $validated['description_fr'] ?? null,
                'en' => $validated['description_en'] ?? $validated['description_fr'] ?? null,
            ],
            'icon' => $validated['icon'] ?? null,
            'image_path' => $imagePath,
            'sort_order' => $validated['sort_order'] ?? 0,
            'published' => $request->boolean('published'),
        ]);

        return redirect()->route('admin.skills.index')->with('success', 'Compétence mise à jour.');
    }

    public function destroy(Skill $skill)
    {
        if ($skill->image_path) {
            Storage::disk('public')->delete($skill->image_path);
        }
        $skill->delete();

        return redirect()->route('admin.skills.index')->with('success', 'Compétence supprimée.');
    }
}
