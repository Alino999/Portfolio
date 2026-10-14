<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::query()->with('category')->orderBy('sort_order')->paginate(10);
        $categories = ProjectCategory::query()->orderBy('sort_order')->get();

        return view('admin.projects.index', compact('projects', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'title_fr' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'short_description_fr' => ['nullable', 'string'],
            'short_description_en' => ['nullable', 'string'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'project_date' => ['nullable', 'date'],
            'client' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'demo_url' => ['nullable', 'url'],
            'github_url' => ['nullable', 'url'],
            'documentation_url' => ['nullable', 'url'],
            'status' => ['nullable', 'string', 'max:50'],
            'project_type' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);

        $titleReference = $validated['title_fr'] ?? $validated['title_en'] ?? 'project';

        Project::query()->create([
            'project_category_id' => $validated['project_category_id'] ?? null,
            'title' => [
                'fr' => $validated['title_fr'],
                'en' => $validated['title_en'] ?? $validated['title_fr'],
            ],
            'slug' => Str::slug($titleReference) . '-' . now()->format('YmdHis') . '-' . random_int(100, 999),
            'short_description' => [
                'fr' => $validated['short_description_fr'] ?? null,
                'en' => $validated['short_description_en'] ?? $validated['short_description_fr'] ?? null,
            ],
            'description' => [
                'fr' => $validated['description_fr'] ?? null,
                'en' => $validated['description_en'] ?? $validated['description_fr'] ?? null,
            ],
            'image_path' => $this->resolveImagePath($request, $validated),
            'gallery' => [],
            'project_date' => $validated['project_date'] ?? null,
            'client' => $validated['client'] ?? null,
            'role' => $validated['role'] ?? null,
            'technologies' => [],
            'features' => [],
            'results' => [],
            'demo_url' => $validated['demo_url'] ?? null,
            'github_url' => $validated['github_url'] ?? null,
            'documentation_url' => $validated['documentation_url'] ?? null,
            'status' => $validated['status'] ?? 'published',
            'is_confidential' => false,
            'project_type' => $validated['project_type'] ?? 'professional',
            'sort_order' => $validated['sort_order'] ?? 0,
            'published' => $request->boolean('published'),
        ]);

        return redirect()->route('admin.projects.index')->with('success', 'Projet ajouté avec succès.');
    }

    public function edit(Project $project)
    {
        $categories = ProjectCategory::query()->orderBy('sort_order')->get();

        return view('admin.projects.edit', compact('project', 'categories'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'title_fr' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'short_description_fr' => ['nullable', 'string'],
            'short_description_en' => ['nullable', 'string'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'project_date' => ['nullable', 'date'],
            'client' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'demo_url' => ['nullable', 'url'],
            'github_url' => ['nullable', 'url'],
            'documentation_url' => ['nullable', 'url'],
            'status' => ['nullable', 'string', 'max:50'],
            'project_type' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);

        $titleReference = $validated['title_fr'] ?? $validated['title_en'] ?? $project->title;

        $project->update([
            'project_category_id' => $validated['project_category_id'] ?? null,
            'title' => [
                'fr' => $validated['title_fr'],
                'en' => $validated['title_en'] ?? $validated['title_fr'],
            ],
            'slug' => Str::slug((string) $titleReference) . '-' . $project->id,
            'short_description' => [
                'fr' => $validated['short_description_fr'] ?? null,
                'en' => $validated['short_description_en'] ?? $validated['short_description_fr'] ?? null,
            ],
            'description' => [
                'fr' => $validated['description_fr'] ?? null,
                'en' => $validated['description_en'] ?? $validated['description_fr'] ?? null,
            ],
            'image_path' => $this->resolveImagePath($request, $validated, $project->image_path),
            'project_date' => $validated['project_date'] ?? null,
            'client' => $validated['client'] ?? null,
            'role' => $validated['role'] ?? null,
            'demo_url' => $validated['demo_url'] ?? null,
            'github_url' => $validated['github_url'] ?? null,
            'documentation_url' => $validated['documentation_url'] ?? null,
            'status' => $validated['status'] ?? 'published',
            'project_type' => $validated['project_type'] ?? 'professional',
            'sort_order' => $validated['sort_order'] ?? 0,
            'published' => $request->boolean('published'),
        ]);

        return redirect()->route('admin.projects.index')->with('success', 'Projet mis à jour.');
    }

    public function destroy(Project $project)
    {
        $this->deleteStoredImage($project->image_path);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projet supprimé.');
    }

    private function resolveImagePath(Request $request, array $validated, ?string $currentPath = null): ?string
    {
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('projects', 'public');
            $this->deleteStoredImage($currentPath);

            return $newImagePath;
        }

        if ($request->boolean('remove_image')) {
            $this->deleteStoredImage($currentPath);

            return null;
        }

        if (! empty($validated['image_path'])) {
            $newPath = $validated['image_path'];
            if ($newPath !== $currentPath) {
                $this->deleteStoredImage($currentPath);
            }

            return $newPath;
        }

        return $currentPath;
    }

    private function deleteStoredImage(?string $imagePath): void
    {
        if ($imagePath && ! Str::startsWith($imagePath, ['http://', 'https://'])) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
