@php
    $profileTitle = App\Support\LocaleHelper::text(['fr' => $profile?->title, 'en' => $profile?->title_en]);
    $profileSubtitle = App\Support\LocaleHelper::text([
        'fr' => $profile?->subtitle ?? 'Consultant informatique',
        'en' => $profile?->subtitle_en,
    ]);
    $cvPath = $profile?->cv_path;
    $cvAvailable = $cvPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($cvPath);
    $primarySkillNames = collect($profile?->primary_skills ?? [])->map(fn ($name) => \Illuminate\Support\Str::lower(trim($name)));
    $heroSkills = $skills->filter(function ($skill) use ($primarySkillNames) {
        $labels = [
            App\Support\LocaleHelper::text($skill->name, 'fr'),
            App\Support\LocaleHelper::text($skill->name, 'en'),
            $skill->category ? App\Support\LocaleHelper::text($skill->category->name, 'fr') : '',
            $skill->category ? App\Support\LocaleHelper::text($skill->category->name, 'en') : '',
        ];

        return collect($labels)->contains(fn ($label) => $label !== '' && $primarySkillNames->contains(\Illuminate\Support\Str::lower(trim($label))));
    });
    if($heroSkills->count() < 3) {
        $remainingSkills = $skills->reject(fn ($skill) => $heroSkills->contains('id', $skill->id));
        $heroSkills = $heroSkills->merge($remainingSkills->take(3 - $heroSkills->count()));
    }
@endphp
<header class="portfolio-hero" data-has-image="{{ $homepageSlides->isNotEmpty() || filled($profile?->avatar_path) ? 'true' : 'false' }}">
    <div class="container portfolio-hero-inner">
        <div class="row align-items-center g-5">
            <div class="col-md-7 order-1" data-reveal>
                <div class="portfolio-hero-copy">
                    <span class="portfolio-eyebrow">{{ $profileTitle !== '' && $profileTitle !== $profileSubtitle ? $profileTitle : __('messages.professional_portfolio') }}</span>
                    @if($profile && ($profile->first_name || $profile->last_name))
                        <h1>{{ trim(($profile->first_name ?? '').' '.($profile->last_name ?? '')) }}</h1>
                    @endif
                    @if($profileSubtitle !== '')<p class="portfolio-hero-title">{{ $profileSubtitle }}</p>@endif
                    @if($profile?->summary || $profile?->summary_en)
                        <p class="portfolio-hero-summary">{{ App\Support\LocaleHelper::text(['fr' => $profile?->summary, 'en' => $profile?->summary_en]) }}</p>
                    @endif
                    <div class="portfolio-hero-actions">
                        @if($sections->contains('key', 'projects') && $projects->isNotEmpty())<a href="#projects" class="btn btn-primary">{{ __('messages.view_projects') }}</a>@endif
                        <a href="#contact" class="btn btn-outline-secondary">{{ __('messages.contact_me') }}</a>
                        @if($cvAvailable)<a href="{{ url('/storage/'.ltrim($cvPath, '/')) }}" class="btn btn-outline-primary" download>{{ __('messages.download_cv') }}</a>@endif
                    </div>
                    @if($heroSkills->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2 mt-4" aria-label="{{ __('messages.skills') }}">
                            @foreach($heroSkills as $heroSkill)<span class="portfolio-chip">{{ App\Support\LocaleHelper::text($heroSkill->name) }}</span>@endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-5 order-2" data-reveal>
                @if($homepageSlides->isNotEmpty())
                    <div id="home-photo-slider" class="carousel slide portfolio-portrait-frame portfolio-carousel" data-bs-ride="carousel" data-bs-interval="5000">
                        @if($homepageSlides->count() > 1)
                            <div class="carousel-indicators">
                                @foreach($homepageSlides as $slide)
                                    <button type="button" data-bs-target="#home-photo-slider" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" @if($loop->first) aria-current="true" @endif aria-label="{{ __('messages.image_number', ['number' => $loop->iteration]) }}"></button>
                                @endforeach
                            </div>
                        @endif
                        <div class="carousel-inner">
                            @foreach($homepageSlides as $slide)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <img src="{{ url('/storage/'.ltrim($slide->file_path, '/')) }}" alt="{{ $slide->alt_text ?: $slide->file_name }}" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                </div>
                            @endforeach
                        </div>
                        @if($homepageSlides->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#home-photo-slider" data-bs-slide="prev" aria-label="{{ __('messages.previous_image') }}"><span class="carousel-control-prev-icon" aria-hidden="true"></span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#home-photo-slider" data-bs-slide="next" aria-label="{{ __('messages.next_image') }}"><span class="carousel-control-next-icon" aria-hidden="true"></span></button>
                        @endif
                    </div>
                @elseif($profile?->avatar_path)
                    <div class="portfolio-portrait-frame" data-reveal>
                        <img src="{{ url('/storage/'.ltrim($profile->avatar_path, '/')) }}" class="portfolio-portrait" alt="{{ trim(($profile->first_name ?? '').' '.($profile->last_name ?? '')) }}" fetchpriority="high">
                    </div>
                @endif

                <div class="portfolio-hero-facts">
                    @if($profile?->location)<div class="portfolio-fact"><span class="portfolio-fact-label">{{ __('messages.location') }}</span><strong class="portfolio-fact-value">{{ $profile->location }}</strong></div>@endif
                    @if($profile?->years_experience)<div class="portfolio-fact"><span class="portfolio-fact-label">{{ __('messages.experience') }}</span><strong class="portfolio-fact-value">{{ $profile->years_experience }}+ {{ __('messages.years') }}</strong></div>@endif
                    @if($profile?->email)<div class="portfolio-fact"><span class="portfolio-fact-label">{{ __('messages.email') }}</span><strong class="portfolio-fact-value">{{ $profile->email }}</strong></div>@endif
                </div>
            </div>
        </div>
    </div>
</header>