<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::query()->orderBy('sort_order')->paginate(10);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTestimonial($request);
        Testimonial::query()->create($this->testimonialData($validated, $request));

        return redirect()->route('admin.testimonials.index')->with('success', 'Témoignage ajouté.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $this->validateTestimonial($request);
        $data = $this->testimonialData($validated, $request);
        if ($request->boolean('published')) {
            $data['submitted_by_client'] = false;
        }
        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Témoignage mis à jour.');
    }

    public function togglePublication(Testimonial $testimonial)
    {
        $testimonial->update([
            'published' => ! $testimonial->published,
            'submitted_by_client' => false,
        ]);

        return redirect()->route('admin.testimonials.index')->with(
            'success',
            $testimonial->published ? 'Témoignage autorisé et publié.' : 'Témoignage masqué.'
        );
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Témoignage supprimé.');
    }

    private function validateTestimonial(Request $request): array
    {
        return $request->validate([
            'author' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'content_fr' => ['required', 'string'],
            'content_en' => ['nullable', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);
    }

    private function testimonialData(array $validated, Request $request): array
    {
        return [
            'author' => $validated['author'],
            'role' => $validated['role'] ?? null,
            'company' => $validated['company'] ?? null,
            'content' => [
                'fr' => $validated['content_fr'],
                'en' => $validated['content_en'] ?? $validated['content_fr'],
            ],
            'rating' => $validated['rating'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'published' => $request->boolean('published'),
        ];
    }
}
