<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResumeTemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResumeTemplateCategoryController extends Controller
{
    public function index()
    {
        $categories = ResumeTemplateCategory::query()
            ->withCount('templates')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.resume-templates.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);
        ResumeTemplateCategory::query()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.resume-template-categories.index')->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, ResumeTemplateCategory $category)
    {
        $validated = $this->validateCategory($request, $category);
        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.resume-template-categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(ResumeTemplateCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.resume-template-categories.index')->with('success', 'Catégorie supprimée. Les modèles associés restent disponibles sans catégorie.');
    }

    private function validateCategory(Request $request, ?ResumeTemplateCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('resume_template_categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
