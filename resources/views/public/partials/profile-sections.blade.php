@if($sections->contains('key', 'about') && ($profile?->bio || $profile?->bio_en))
    <section class="portfolio-section" id="about">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7" data-reveal>
                    <div class="portfolio-section-heading">
                        <span class="portfolio-eyebrow">{{ __('messages.about') }}</span>
                        <h2>{{ __('messages.about_heading') }}</h2>
                    </div>
                    <div class="portfolio-panel"><p class="mb-0 lh-lg">{{ App\Support\LocaleHelper::text(['fr' => $profile?->bio, 'en' => $profile?->bio_en]) }}</p></div>
                </div>
                <div class="col-lg-5" data-reveal>
                    <div class="row g-3">
                        @if($profile?->years_experience)<div class="col-6"><div class="portfolio-panel"><div class="portfolio-stat">{{ $profile->years_experience }}+</div><div class="portfolio-stat-label">{{ __('messages.years_experience') }}</div></div></div>@endif
                        @if($projects->isNotEmpty())<div class="col-6"><div class="portfolio-panel"><div class="portfolio-stat">{{ $projects->count() }}</div><div class="portfolio-stat-label">{{ __('messages.projects') }}</div></div></div>@endif
                        @if($skills->isNotEmpty())<div class="col-6"><div class="portfolio-panel"><div class="portfolio-stat">{{ $skills->count() }}</div><div class="portfolio-stat-label">{{ __('messages.skills') }}</div></div></div>@endif
                        @if($services->isNotEmpty())<div class="col-6"><div class="portfolio-panel"><div class="portfolio-stat">{{ $services->count() }}</div><div class="portfolio-stat-label">{{ __('messages.services') }}</div></div></div>@endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

@if($sections->contains('key', 'skills') && $skills->isNotEmpty())
    <section class="portfolio-section portfolio-section-alt" id="skills">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal>
                <span class="portfolio-eyebrow">{{ __('messages.expertise') }}</span>
                <h2>{{ __('messages.skills') }}</h2>
            </div>
            <x-portfolio-carousel :label="__('messages.skills')">
                @foreach($skills as $skill)
                        <article class="portfolio-carousel-slide" data-reveal>
                            <div class="portfolio-skill-card">
                            @if($skill->image_path)
                                <img class="portfolio-skill-image" src="{{ asset('storage/'.$skill->image_path) }}" alt="{{ App\Support\LocaleHelper::text($skill->name) }}">
                            @endif
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    @if($skill->category)<div class="small text-secondary mb-1">{{ App\Support\LocaleHelper::text($skill->category->name) }}</div>@endif
                                    <h3 class="h5 mb-1">{{ App\Support\LocaleHelper::text($skill->name) }}</h3>
                                </div>
                                @if($skill->level || $skill->level_en)<span class="portfolio-chip">{{ App\Support\LocaleHelper::text(['fr' => $skill->level, 'en' => $skill->level_en]) }}</span>@endif
                            </div>
                            @if($skill->description)<p class="small text-secondary mt-2 mb-2">{{ App\Support\LocaleHelper::text($skill->description) }}</p>@endif
                            @if($skill->percentage !== null)
                                <div class="progress mt-3" role="progressbar" aria-label="{{ App\Support\LocaleHelper::text($skill->name) }}" aria-valuenow="{{ $skill->percentage }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $skill->percentage }}%"></div>
                                </div>
                            @endif
                            </div>
                        </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if($technologies->isNotEmpty())
    <section class="portfolio-section" id="technologies">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal><span class="portfolio-eyebrow">{{ __('messages.technologies') }}</span><h2>{{ __('messages.technologies') }}</h2></div>
            <x-portfolio-carousel :label="__('messages.technologies')">
                @foreach($technologies as $technology)
                    <article class="portfolio-carousel-slide" data-reveal>
                        <div class="portfolio-panel">
                            @if($technology->image_path)<img src="{{ asset('storage/'.$technology->image_path) }}" class="portfolio-entry-image" alt="{{ App\Support\LocaleHelper::text($technology->name) }}" loading="lazy">@endif
                            @if($technology->icon)<div class="portfolio-service-icon mb-3" aria-hidden="true">{{ $technology->icon }}</div>@endif
                            <h3 class="h5 mb-1">{{ App\Support\LocaleHelper::text($technology->name) }}</h3>
                            @if($technology->category)<span class="small text-secondary">{{ $technology->category }}</span>@endif
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if($sections->contains('key', 'experience') && $experiences->isNotEmpty())
    <section class="portfolio-section" id="experience">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal>
                <span class="portfolio-eyebrow">{{ __('messages.career') }}</span>
                <h2>{{ __('messages.experience') }}</h2>
            </div>
            <x-portfolio-carousel :label="__('messages.experience')">
                @foreach($experiences as $experience)
                    @php
                        $responsibilities = $experience->responsibilities ?? [];
                        $responsibilities = $responsibilities[app()->getLocale()] ?? $responsibilities['fr'] ?? $responsibilities;
                        $responsibilities = is_array($responsibilities) ? $responsibilities : [$responsibilities];
                        $experienceDescription = $experience->description ?? null;
                        if (is_array($experienceDescription)) {
                            $experienceDescription = $experienceDescription[app()->getLocale()] ?? $experienceDescription['fr'] ?? $experienceDescription;
                        }
                    @endphp
                    <article class="portfolio-carousel-slide" data-reveal>
                        <div class="portfolio-panel">
                        @php $experienceImage = $experience->image_path ?: $experience->logo_path; @endphp
                        @if($experienceImage)
                            @php $experienceImageUrl = \Illuminate\Support\Str::startsWith($experienceImage, ['http://', 'https://']) ? $experienceImage : asset('storage/'.$experienceImage); @endphp
                            <img src="{{ $experienceImageUrl }}" class="portfolio-entry-image" alt="{{ App\Support\LocaleHelper::text(['fr' => $experience->company, 'en' => $experience->company_en]) }}" loading="lazy">
                        @endif
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <h3 class="h5 mb-1">{{ App\Support\LocaleHelper::text(['fr' => $experience->position, 'en' => $experience->position_en]) }}</h3>
                                <div class="fw-semibold">{{ App\Support\LocaleHelper::text(['fr' => $experience->company, 'en' => $experience->company_en]) }}@if($experience->location) · {{ $experience->location }}@endif</div>
                            </div>
                            @php
                                $startDateLabel = $experience->start_date?->format('m-d') === '01-01'
                                    ? $experience->start_date->format('Y')
                                    : $experience->start_date?->translatedFormat('M Y');
                                $endDateLabel = $experience->end_date?->format('m-d') === '01-01'
                                    ? $experience->end_date->format('Y')
                                    : $experience->end_date?->translatedFormat('M Y');
                                $yearOnlyPeriod = $experience->start_date?->format('m-d') === '01-01'
                                    && $experience->end_date?->format('m-d') === '01-01'
                                    && $experience->start_date?->year === $experience->end_date?->year;
                            @endphp
                            <span class="small text-secondary">
                                @if($yearOnlyPeriod)
                                    {{ $experience->start_date->format('Y') }}
                                @else
                                    {{ $startDateLabel }}{{ $experience->is_current ? ' – '.__('messages.present') : ($endDateLabel ? ' – '.$endDateLabel : '') }}
                                @endif
                            </span>
                        </div>
                        @if($experienceDescription)<p class="mt-3 mb-0">{{ is_array($experienceDescription) ? implode(' ', $experienceDescription) : $experienceDescription }}</p>@endif
                        @if(!empty($responsibilities))<ul class="mt-3 mb-0">@foreach($responsibilities as $responsibility)<li>{{ $responsibility }}</li>@endforeach</ul>@endif
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@php $profileEducations = $profile?->educations?->where('published', true) ?? collect(); @endphp
@if($profileEducations->isNotEmpty())
    <section class="portfolio-section portfolio-section-alt" id="education">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal>
                <span class="portfolio-eyebrow">{{ __('messages.education') }}</span>
                <h2>{{ __('messages.education_heading') }}</h2>
            </div>
            <x-portfolio-carousel :label="__('messages.education')">
                @foreach($profileEducations as $education)
                    <article class="portfolio-carousel-slide" data-reveal>
                        <div class="portfolio-panel">
                        @php
                            $educationDegree = app()->getLocale() === 'en' ? ($education->degree_en ?: $education->degree) : $education->degree;
                            $educationField = app()->getLocale() === 'en' ? ($education->field_en ?: $education->field) : $education->field;
                        @endphp
                        @if($education->image_path)<img src="{{ asset('storage/'.$education->image_path) }}" class="portfolio-entry-image" alt="{{ $education->school }}" loading="lazy">@endif
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div>
                                @if($educationDegree)<h3 class="h5 mb-1">{{ $educationDegree }}</h3>@endif
                                @if($educationField)<div class="text-secondary">{{ $educationField }}</div>@endif
                                @if($education->school)<div class="fw-semibold mt-1">{{ $education->school }}</div>@endif
                            </div>
                            @if($education->start_date || $education->end_date)<span class="small text-secondary">{{ $education->start_date?->format('Y') }}@if($education->end_date && $education->end_date->year !== $education->start_date?->year) – {{ $education->end_date->format('Y') }}@endif</span>@endif
                        </div>
                        @if($education->description)<p class="mt-3 mb-0">{{ App\Support\LocaleHelper::text($education->description) }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if(($profile?->achievements?->where('published', true) ?? collect())->isNotEmpty())
    <section class="portfolio-section" id="achievements">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal><span class="portfolio-eyebrow">{{ app()->getLocale() === 'fr' ? 'Parcours' : 'Highlights' }}</span><h2>{{ app()->getLocale() === 'fr' ? 'Réalisations' : 'Achievements' }}</h2></div>
            <x-portfolio-carousel :label="app()->getLocale() === 'fr' ? 'Réalisations' : 'Achievements'">
                @foreach($profile->achievements->where('published', true) as $achievement)
                    <article class="portfolio-carousel-slide" data-reveal>
                        <div class="portfolio-panel">
                            @if($achievement->image_path)<img src="{{ asset('storage/'.$achievement->image_path) }}" class="portfolio-entry-image" alt="{{ App\Support\LocaleHelper::text($achievement->title) }}" loading="lazy">@endif
                            <h3 class="h5">{{ App\Support\LocaleHelper::text($achievement->title) }}</h3>
                            @if($achievement->achievement_date)<div class="small text-secondary mb-2">{{ $achievement->achievement_date->format('Y') }}</div>@endif
                            @if($achievement->description)<p class="text-secondary">{{ App\Support\LocaleHelper::text($achievement->description) }}</p>@endif
                            @if($achievement->url)<a class="btn btn-sm btn-outline-primary" href="{{ $achievement->url }}" target="_blank" rel="noopener noreferrer">{{ app()->getLocale() === 'fr' ? 'Voir la réalisation' : 'View achievement' }} ↗</a>@endif
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if(($profile?->professionalReferences?->where('published', true) ?? collect())->isNotEmpty())
    <section class="portfolio-section portfolio-section-alt" id="references">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal><span class="portfolio-eyebrow">{{ app()->getLocale() === 'fr' ? 'Recommandations' : 'Recommendations' }}</span><h2>{{ app()->getLocale() === 'fr' ? 'Références professionnelles' : 'Professional references' }}</h2></div>
            <x-portfolio-carousel :label="app()->getLocale() === 'fr' ? 'Références professionnelles' : 'Professional references'">
                @foreach($profile->professionalReferences->where('published', true) as $reference)
                    <article class="portfolio-carousel-slide" data-reveal>
                        <div class="portfolio-panel">
                            @if($reference->image_path)<img src="{{ asset('storage/'.$reference->image_path) }}" class="portfolio-entry-image" alt="{{ $reference->name }}" loading="lazy">@endif
                            <h3 class="h5">{{ $reference->name }}</h3>
                            @php $referenceRole = app()->getLocale() === 'en' ? ($reference->role_en ?: $reference->role) : $reference->role; @endphp
                            @if($referenceRole)<div class="fw-semibold">{{ $referenceRole }}</div>@endif
                            @if($reference->company)<div class="text-secondary">{{ $reference->company }}</div>@endif
                            @if($reference->email)<a class="d-block mt-3" href="mailto:{{ $reference->email }}">{{ $reference->email }}</a>@endif
                            @if($reference->phone)<a class="d-block" href="tel:{{ $reference->phone }}">{{ $reference->phone }}</a>@endif
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if(($profile?->socialLinks?->where('is_active', true) ?? collect())->isNotEmpty())
    <section class="portfolio-section" id="social-links">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal><span class="portfolio-eyebrow">{{ app()->getLocale() === 'fr' ? 'En ligne' : 'Online' }}</span><h2>{{ app()->getLocale() === 'fr' ? 'Réseaux sociaux' : 'Social networks' }}</h2></div>
            <x-portfolio-carousel :label="app()->getLocale() === 'fr' ? 'Réseaux sociaux' : 'Social networks'">
                @foreach($profile->socialLinks->where('is_active', true) as $socialLink)
                    @php
                        $socialPlatform = strtolower($socialLink->platform);
                        $socialBrandIcons = ['github' => 'bi-github', 'linkedin' => 'bi-linkedin', 'facebook' => 'bi-facebook'];
                    @endphp
                    <article class="portfolio-carousel-slide portfolio-social-slide portfolio-compact-slide" data-reveal>
                        <div class="portfolio-panel portfolio-social-card">
                            <div class="portfolio-social-visual">
                                @if($socialLink->image_path)
                                    <img src="{{ asset('storage/'.$socialLink->image_path) }}" class="portfolio-social-image" alt="{{ $socialLink->label ?: $socialLink->platform }}" loading="lazy">
                                @elseif(isset($socialBrandIcons[$socialPlatform]))
                                    <i class="bi {{ $socialBrandIcons[$socialPlatform] }} portfolio-social-logo portfolio-social-logo-{{ $socialPlatform }}" aria-hidden="true"></i>
                                @elseif($socialLink->icon)
                                    <span class="portfolio-social-logo" aria-hidden="true">{{ $socialLink->icon }}</span>
                                @endif
                            </div>
                            <div class="portfolio-social-details">
                                <h3 class="h5 mb-2">{{ $socialLink->label ?: $socialLink->platform }}</h3>
                                <a class="btn btn-sm btn-outline-primary" href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer">{{ app()->getLocale() === 'fr' ? 'Visiter le profil' : 'Visit profile' }} ↗</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif