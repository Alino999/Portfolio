<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Testimonial;
use App\Services\PortfolioDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicPortfolioController extends Controller
{
    public function __construct(
        protected PortfolioDataService $portfolioDataService,
    ) {}

    public function index(Request $request)
    {
        $profile = $this->portfolioDataService->profile();
        $settings = $this->portfolioDataService->siteSettings();
        $sections = $this->portfolioDataService->sections();
        $skills = $this->portfolioDataService->skills();
        $experiences = $this->portfolioDataService->experiences();
        $projects = $this->portfolioDataService->projects();
        $services = $this->portfolioDataService->services();
        $certifications = $this->portfolioDataService->certifications();
        $technologies = $this->portfolioDataService->technologies();
        $languages = $this->portfolioDataService->languages();
        $testimonials = $this->portfolioDataService->testimonials();
        $visitorToken = $request->cookie('article_like_visitor');
        $visitorHash = is_string($visitorToken) && preg_match('/^[a-f0-9]{64}$/i', $visitorToken)
            ? hash('sha256', $visitorToken)
            : null;
        $articles = $this->portfolioDataService->articles($visitorHash);
        $homepageSlides = $this->portfolioDataService->homepageSlides();

        return view('public.index', compact(
            'profile',
            'settings',
            'sections',
            'skills',
            'experiences',
            'projects',
            'services',
            'certifications',
            'technologies',
            'languages',
            'testimonials',
            'articles',
            'homepageSlides',
        ));
    }

    public function contact(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10'],
        ]);

        ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'new',
        ]);

        return back()->with('success', __('messages.contact_success'));
    }

    public function submitTestimonial(Request $request)
    {
        $validated = $request->validate([
            'author' => ['required', 'string', 'min:2', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $locale = in_array(app()->getLocale(), ['fr', 'en'], true) ? app()->getLocale() : 'fr';

        Testimonial::query()->create([
            'author' => $validated['author'],
            'role' => $validated['role'] ?? null,
            'company' => $validated['company'] ?? null,
            'content' => [$locale => $validated['content']],
            'rating' => $validated['rating'],
            'sort_order' => 0,
            'published' => false,
            'submitted_by_client' => true,
        ]);

        return redirect()->to(route('home').'#testimonials')->with('testimonial_pending', true);
    }
}
