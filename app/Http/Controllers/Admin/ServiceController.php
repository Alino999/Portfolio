<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::query()->orderBy('sort_order')->paginate(10);

        return view('admin.services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateService($request);
        Service::query()->create($this->serviceData($validated, $request));

        return redirect()->route('admin.services.index')->with('success', 'Service ajouté.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $this->validateService($request);
        $service->update($this->serviceData($validated, $request, $service));

        return redirect()->route('admin.services.index')->with('success', 'Service mis à jour.');
    }

    public function destroy(Service $service)
    {
        if ($service->image_path) {
            Storage::disk('public')->delete($service->image_path);
        }
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service supprimé.');
    }

    private function validateService(Request $request): array
    {
        return $request->validate([
            'title_fr' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description_fr' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);
    }

    private function serviceData(array $validated, Request $request, ?Service $service = null): array
    {
        $imagePath = $service?->image_path;
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('services', 'public');
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
            'title' => [
                'fr' => $validated['title_fr'],
                'en' => $validated['title_en'] ?? $validated['title_fr'],
            ],
            'description' => [
                'fr' => $validated['description_fr'] ?? null,
                'en' => $validated['description_en'] ?? $validated['description_fr'] ?? null,
            ],
            'icon' => $validated['icon'] ?? null,
            'image_path' => $imagePath,
            'sort_order' => $validated['sort_order'] ?? 0,
            'published' => $request->boolean('published'),
        ];
    }
}
