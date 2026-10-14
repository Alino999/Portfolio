<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeSlideController extends Controller
{
    private const CATEGORY = 'homepage_slider';

    public function index()
    {
        $slides = Media::query()
            ->where('category', self::CATEGORY)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10);

        return view('admin.home-slides.index', compact('slides'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $sortOrder = (int) Media::query()->where('category', self::CATEGORY)->max('sort_order');

        foreach ($validated['photos'] as $photo) {
            $path = $photo->store('homepage-slides', 'public');
            $fileName = $photo->getClientOriginalName();

            Media::query()->create([
                'title' => pathinfo($fileName, PATHINFO_FILENAME),
                'file_name' => $fileName,
                'file_path' => $path,
                'mime_type' => $photo->getMimeType(),
                'file_size' => $photo->getSize() ?? 0,
                'category' => self::CATEGORY,
                'alt_text' => pathinfo($fileName, PATHINFO_FILENAME),
                'sort_order' => ++$sortOrder,
                'is_public' => true,
            ]);
        }

        return redirect()->route('admin.home-slides.index')->with('success', 'Photos ajoutées au diaporama.');
    }

    public function destroy(Media $media)
    {
        abort_unless($media->category === self::CATEGORY, 404);

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return redirect()->route('admin.home-slides.index')->with('success', 'Photo supprimée du diaporama.');
    }
}