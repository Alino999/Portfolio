@if($sections->contains('key', 'services') && $services->isNotEmpty())
    <section class="portfolio-section portfolio-section-alt" id="services">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal>
                <span class="portfolio-eyebrow">{{ __('messages.services') }}</span>
                <h2>{{ __('messages.services_heading') }}</h2>
            </div>
            <x-portfolio-carousel :label="__('messages.services')">
                @foreach($services as $service)
                    <article class="portfolio-carousel-slide portfolio-service-slide portfolio-compact-slide" data-reveal>
                        <div class="portfolio-panel portfolio-service-card">
                            <div class="portfolio-service-visual">
                                @if($service->image_path)
                                    <img src="{{ asset('storage/'.$service->image_path) }}" class="portfolio-service-image" alt="{{ App\Support\LocaleHelper::text($service->title) }}" loading="lazy">
                                @elseif($service->icon)
                                    <div class="portfolio-service-icon" aria-hidden="true">{{ $service->icon }}</div>
                                @endif
                            </div>
                            <div class="portfolio-service-details">
                                <h3 class="h5 mb-2">{{ App\Support\LocaleHelper::text($service->title) }}</h3>
                                @if($service->description)<p class="text-secondary mb-0">{{ App\Support\LocaleHelper::text($service->description) }}</p>@endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if($sections->contains('key', 'projects') && $projects->isNotEmpty())
    <section class="portfolio-section" id="projects">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal>
                <span class="portfolio-eyebrow">{{ __('messages.selected_work') }}</span>
                <h2>{{ __('messages.projects') }}</h2>
                <p>{{ __('messages.projects_intro') }}</p>
            </div>

            @php $projectCategories = $projects->pluck('category')->filter()->unique('id'); @endphp
            <div class="portfolio-project-toolbar" data-reveal>
                <label class="visually-hidden" for="project-search">{{ __('messages.search_projects') }}</label>
                <input id="project-search" type="search" class="form-control" data-project-search placeholder="{{ __('messages.search_projects') }}">
                <button type="button" class="btn btn-outline-secondary portfolio-filter-button" data-project-filter="all" aria-pressed="true">{{ __('messages.all_projects') }}</button>
                @foreach($projectCategories as $category)
                    <button type="button" class="btn btn-outline-secondary portfolio-filter-button" data-project-filter="{{ $category->slug }}" aria-pressed="false">{{ App\Support\LocaleHelper::text($category->name) }}</button>
                @endforeach
            </div>

            <x-portfolio-carousel :label="__('messages.projects')">
                @foreach($projects as $project)
                    @php
                        $projectTitle = App\Support\LocaleHelper::text($project->title);
                        $projectDescription = App\Support\LocaleHelper::text($project->short_description);
                        $projectCategory = $project->category?->slug ?? 'uncategorized';
                        $projectImageUrl = $project->image_path
                            ? (\Illuminate\Support\Str::startsWith($project->image_path, ['http://', 'https://']) ? $project->image_path : url('/storage/'.ltrim($project->image_path, '/')))
                            : null;
                        $projectGallery = collect($project->gallery ?? [])->flatten()->filter(fn ($path) => is_string($path) && trim($path) !== '')->values();
                        $projectFeatures = $project->features ?? [];
                        $projectFeatures = $projectFeatures[app()->getLocale()] ?? $projectFeatures['fr'] ?? $projectFeatures;
                        $projectResults = $project->results ?? [];
                        $projectResults = $projectResults[app()->getLocale()] ?? $projectResults['fr'] ?? $projectResults;
                    @endphp
                    <div class="portfolio-carousel-slide" data-project-card data-project-category="{{ $projectCategory }}" data-project-search-text="{{ $projectTitle }} {{ $projectDescription }} {{ $project->client }} {{ implode(' ', $project->technologies ?? []) }}">
                        <article class="portfolio-project-card" data-reveal>
                            @if($projectImageUrl)<img src="{{ $projectImageUrl }}" class="portfolio-project-image" alt="{{ $projectTitle }}" loading="lazy">@endif
                            <div class="portfolio-project-body">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    @if($project->category)<span class="portfolio-chip">{{ App\Support\LocaleHelper::text($project->category->name) }}</span>@endif
                                    @if($project->project_date)<span class="small text-secondary">{{ \Illuminate\Support\Carbon::parse($project->project_date)->format('Y') }}</span>@endif
                                </div>
                                <h3 class="h5">{{ $projectTitle }}</h3>
                                @if($projectDescription)<p class="text-secondary">{{ $projectDescription }}</p>@endif
                                @if($project->technologies)<div class="d-flex flex-wrap gap-2 mb-3">@foreach($project->technologies as $technology)<span class="portfolio-chip">{{ $technology }}</span>@endforeach</div>@endif
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#project-modal-{{ $project->id }}">{{ __('messages.project_details') }}</button>
                                    @if($project->demo_url)<a class="btn btn-sm btn-outline-secondary" href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.view_project') }} ↗</a>@endif
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="modal fade" id="project-modal-{{ $project->id }}" tabindex="-1" aria-labelledby="project-modal-title-{{ $project->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                            <div class="modal-content portfolio-project-modal">
                                <div class="modal-header">
                                    <div>
                                        @if($project->category)<div class="portfolio-eyebrow mb-1">{{ App\Support\LocaleHelper::text($project->category->name) }}</div>@endif
                                        <h2 class="modal-title h4" id="project-modal-title-{{ $project->id }}">{{ $projectTitle }}</h2>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                                </div>
                                <div class="modal-body">
                                    @if($projectImageUrl)<img src="{{ $projectImageUrl }}" class="portfolio-project-image mb-4" alt="{{ $projectTitle }}" loading="lazy">@endif
                                    @php $fullDescription = App\Support\LocaleHelper::text($project->description); @endphp
                                    @if($fullDescription)<p class="lh-lg">{{ $fullDescription }}</p>@endif
                                    @if($project->client || $project->role)<dl class="row small">@if($project->client)<dt class="col-sm-3">{{ __('messages.client') }}</dt><dd class="col-sm-9">{{ $project->client }}</dd>@endif @if($project->role)<dt class="col-sm-3">{{ __('messages.role') }}</dt><dd class="col-sm-9">{{ $project->role }}</dd>@endif</dl>@endif
                                    @if($project->technologies)<h3 class="h6">{{ __('messages.technologies') }}</h3><div class="d-flex flex-wrap gap-2 mb-3">@foreach($project->technologies as $technology)<span class="portfolio-chip">{{ $technology }}</span>@endforeach</div>@endif
                                    @if(is_array($projectFeatures) && count($projectFeatures))<h3 class="h6">{{ __('messages.project_features') }}</h3><ul>@foreach($projectFeatures as $feature)<li>{{ $feature }}</li>@endforeach</ul>@endif
                                    @if(is_array($projectResults) && count($projectResults))<h3 class="h6">{{ __('messages.project_results') }}</h3><ul>@foreach($projectResults as $result)<li>{{ $result }}</li>@endforeach</ul>@endif
                                    @if($projectGallery->isNotEmpty())<div class="row g-2 mt-3">@foreach($projectGallery as $galleryImage)@php $galleryUrl = \Illuminate\Support\Str::startsWith($galleryImage, ['http://', 'https://']) ? $galleryImage : url('/storage/'.ltrim($galleryImage, '/')); @endphp<div class="col-6"><img src="{{ $galleryUrl }}" class="portfolio-project-image" alt="{{ $projectTitle }}" loading="lazy"></div>@endforeach</div>@endif
                                </div>
                                <div class="modal-footer">
                                    @if($project->github_url)<a class="btn btn-outline-secondary" href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer">GitHub ↗</a>@endif
                                    @if($project->documentation_url)<a class="btn btn-outline-secondary" href="{{ $project->documentation_url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.documentation') }} ↗</a>@endif
                                    @if($project->demo_url)<a class="btn btn-primary" href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.view_project') }} ↗</a>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-portfolio-carousel>
            <p class="portfolio-empty-state mt-4" data-project-empty hidden>{{ __('messages.no_matching_projects') }}</p>
        </div>
    </section>
@endif