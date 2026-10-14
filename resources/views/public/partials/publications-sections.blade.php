@if($sections->contains('key', 'certifications') && $certifications->isNotEmpty())
    <section class="portfolio-section" id="certifications">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal><span class="portfolio-eyebrow">{{ __('messages.certifications') }}</span><h2>{{ __('messages.certifications_heading') }}</h2></div>
            <x-portfolio-carousel :label="__('messages.certifications')">
                @foreach($certifications as $certification)
                    <article class="portfolio-carousel-slide portfolio-certification-slide portfolio-compact-slide" data-reveal>
                        <div class="portfolio-panel portfolio-certification-card">
                            <div class="portfolio-certification-visual">
                                @if($certification->image_path)
                                    <img src="{{ asset('storage/'.$certification->image_path) }}" class="portfolio-certification-image" alt="{{ App\Support\LocaleHelper::text($certification->title) }}" loading="lazy">
                                @else
                                    <span class="portfolio-certification-mark" aria-hidden="true">✓</span>
                                @endif
                            </div>
                            <div class="portfolio-certification-details">
                                <h3 class="h5 mb-2">{{ App\Support\LocaleHelper::text($certification->title) }}</h3>
                                @if($certification->issuer || $certification->issuer_en)<p class="text-secondary mb-2">{{ App\Support\LocaleHelper::text(['fr' => $certification->issuer, 'en' => $certification->issuer_en]) }}</p>@endif
                                @if($certification->issue_date)
                                    <div class="small text-secondary">
                                        {{ $certification->issue_date->day === 1 ? $certification->issue_date->translatedFormat('F Y') : $certification->issue_date->translatedFormat('j F Y') }}
                                    </div>
                                @endif
                                @if($certification->url)<a class="btn btn-sm btn-outline-secondary mt-3" href="{{ $certification->url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.view_certificate') }} ↗</a>@endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if($languages->isNotEmpty())
    <section class="portfolio-section portfolio-section-alt" id="languages">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal><span class="portfolio-eyebrow">{{ __('messages.languages') }}</span><h2>{{ __('messages.languages_heading') }}</h2></div>
            <x-portfolio-carousel :label="__('messages.languages')">
                @foreach($languages as $language)
                    @php
                        $levelEn = [
                            'Langue maternelle' => 'Native',
                            'Courant' => 'Fluent',
                            'Avancé' => 'Advanced',
                            'Intermédiaire' => 'Intermediate',
                            'Débutant' => 'Beginner',
                        ];
                        $languageLevel = app()->getLocale() === 'en' ? ($language->level_en ?: ($levelEn[$language->level] ?? $language->level)) : $language->level;
                    @endphp
                    <article class="portfolio-carousel-slide" data-reveal><div class="portfolio-panel">
                        @if($language->image_path)<img src="{{ asset('storage/'.$language->image_path) }}" class="portfolio-entry-image" alt="{{ App\Support\LocaleHelper::text($language->name) }}" loading="lazy">@endif
                        <div class="d-flex justify-content-between align-items-center gap-3"><h3 class="h5 mb-0">{{ App\Support\LocaleHelper::text($language->name) }}</h3>@if($languageLevel)<span class="portfolio-chip">{{ $languageLevel }}</span>@endif</div>
                    </div></article>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if($sections->contains('key', 'blog') && $articles->isNotEmpty())
    <section class="portfolio-section" id="blog">
        <div class="container">
            <div class="portfolio-section-heading" data-reveal><span class="portfolio-eyebrow">{{ __('messages.blog') }}</span><h2>{{ __('messages.blog_heading') }}</h2></div>
            <x-portfolio-carousel :label="__('messages.blog')">
                @foreach($articles as $article)
                    @php
                        $articleTitle = App\Support\LocaleHelper::text($article->title);
                        $articleImageUrl = $article->image_path
                            ? (\Illuminate\Support\Str::startsWith($article->image_path, ['http://', 'https://']) ? $article->image_path : url('/storage/'.ltrim($article->image_path, '/')))
                            : null;
                    @endphp
                    <article class="portfolio-carousel-slide portfolio-blog-slide" data-reveal>
                        <div class="portfolio-project-card portfolio-blog-card h-100">
                            @if($articleImageUrl)<img src="{{ $articleImageUrl }}" class="portfolio-project-image" alt="{{ $articleTitle }}" loading="lazy">@endif
                            <div class="portfolio-project-body">
                                @if($article->category)<span class="portfolio-chip mb-3">{{ App\Support\LocaleHelper::text($article->category->name) }}</span>@endif
                                <h3 class="h5 mt-2">
                                    <button type="button" class="btn btn-link p-0 text-reset text-start" data-bs-toggle="modal" data-bs-target="#article-modal-{{ $article->id }}">{{ $articleTitle }}</button>
                                </h3>
                                @if($article->excerpt)<p class="text-secondary portfolio-blog-excerpt">{{ App\Support\LocaleHelper::text($article->excerpt) }}</p>@endif
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#article-modal-{{ $article->id }}">{{ __('messages.read_article') }} →</button>
                            </div>
                        </div>
                    </article>

                    <div class="modal fade" id="article-modal-{{ $article->id }}" tabindex="-1" aria-labelledby="article-modal-title-{{ $article->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                            <div class="modal-content portfolio-project-modal">
                                <div class="modal-header">
                                    <div>
                                        @if($article->category)<div class="portfolio-eyebrow mb-1">{{ App\Support\LocaleHelper::text($article->category->name) }}</div>@endif
                                        <h2 class="modal-title h4" id="article-modal-title-{{ $article->id }}">{{ $articleTitle }}</h2>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                                </div>
                                <div class="modal-body">
                                    @if($articleImageUrl)<img src="{{ $articleImageUrl }}" class="portfolio-project-image mb-4" alt="{{ $articleTitle }}" loading="lazy">@endif
                                    @if($article->excerpt)<p class="text-secondary lh-lg">{{ App\Support\LocaleHelper::text($article->excerpt) }}</p>@endif
                                    <div class="lh-lg" style="white-space: pre-line;">{{ App\Support\LocaleHelper::text($article->content) }}</div>
                                    <section class="interaction-bar py-3 mt-4 d-flex flex-wrap align-items-center gap-2">
                                        @php $hasLiked = $article->relationLoaded('likes') && $article->likes->isNotEmpty(); @endphp
                                        <form method="POST" action="{{ route('public.articles.like', $article->slug) }}">
                                            @csrf
                                            <button class="btn {{ $hasLiked ? 'btn-primary' : 'btn-outline-primary' }}" type="submit">{{ $hasLiked ? __('messages.liked') : __('messages.like') }} · {{ $article->likes_count }}</button>
                                        </form>
                                    </section>
                                    <section class="mt-4">
                                        <h3 class="h5 mb-3">{{ __('messages.comments') }} ({{ $article->comments->count() }})</h3>
                                        @forelse($article->comments as $comment)
                                            <article class="border-bottom py-3">
                                                <div class="fw-semibold">{{ $comment->author_name }}</div>
                                                <p class="mb-0 mt-1" style="white-space: pre-line">{{ $comment->content }}</p>
                                            </article>
                                        @empty
                                            <p class="text-secondary">{{ __('messages.no_comments') }}</p>
                                        @endforelse
                                        <form method="POST" action="{{ route('public.articles.comments.store', $article->slug) }}" class="mt-4">
                                            @csrf
                                            <div class="mb-3"><label class="form-label" for="comment-name-{{ $article->id }}">{{ __('messages.comment_name') }}</label><input id="comment-name-{{ $article->id }}" name="author_name" class="form-control" maxlength="100" required></div>
                                            <div class="mb-3"><label class="form-label" for="comment-content-{{ $article->id }}">{{ __('messages.comment_placeholder') }}</label><textarea id="comment-content-{{ $article->id }}" name="content" class="form-control" rows="3" maxlength="2000" required></textarea></div>
                                            <button class="btn btn-primary" type="submit">{{ __('messages.send_comment') }}</button>
                                        </form>
                                    </section>
                                </div>
                                <div class="modal-footer">
                                    @if($article->source_url)<a class="btn btn-outline-secondary" href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.original_source') }} ↗</a>@endif
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-portfolio-carousel>
        </div>
    </section>
@endif

@if($sections->contains('key', 'testimonials'))
    <section class="portfolio-section portfolio-section-alt" id="testimonials">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-4" data-reveal>
                <div class="portfolio-section-heading mb-0"><span class="portfolio-eyebrow">{{ __('messages.testimonials') }}</span><h2>{{ __('messages.testimonials_heading') }}</h2></div>
                <button type="button" class="btn btn-primary rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0 p-0 testimonial-add-button" data-bs-toggle="modal" data-bs-target="#testimonial-modal" aria-label="{{ __('messages.leave_testimonial') }}" title="{{ __('messages.leave_testimonial') }}">
                    <span aria-hidden="true">+</span>
                </button>
            </div>
            @if($testimonials->isNotEmpty())
                <x-portfolio-carousel :label="__('messages.testimonials')">
                    @foreach($testimonials as $testimonial)
                        <div class="portfolio-carousel-slide" data-reveal>
                            <figure class="portfolio-panel mb-0">
                                <div class="testimonial-rating-display" role="img" aria-label="{{ $testimonial->rating }} / 5">
                                    @for($star = 1; $star <= 5; $star++)
                                        <span class="{{ $star <= $testimonial->rating ? 'is-filled' : 'is-empty' }}" aria-hidden="true">★</span>
                                    @endfor
                                </div>
                                <blockquote class="mb-3">“{{ App\Support\LocaleHelper::text($testimonial->content) }}”</blockquote>
                                <figcaption class="fw-semibold">{{ $testimonial->author }}@if($testimonial->role) <span class="text-secondary fw-normal">· {{ $testimonial->role }}</span>@endif</figcaption>
                                @if($testimonial->company)<div class="small text-secondary mt-1">{{ $testimonial->company }}</div>@endif
                            </figure>
                        </div>
                    @endforeach
                </x-portfolio-carousel>
            @endif

            @if(session('testimonial_pending'))<div class="alert alert-success mt-4" role="status">{{ __('messages.testimonial_pending') }}</div>@endif

            <div class="modal fade" id="testimonial-modal" tabindex="-1" aria-labelledby="testimonial-modal-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title h5" id="testimonial-modal-title">{{ __('messages.leave_testimonial') }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-secondary small">{{ __('messages.testimonial_moderation') }}</p>
                            <form method="POST" action="{{ route('public.testimonials.store') }}" class="portfolio-contact-form" data-testimonial-form>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="testimonial-author">{{ __('messages.testimonial_name') }}</label>
                                        <input id="testimonial-author" name="author" class="form-control" maxlength="255" value="{{ old('author') }}" required>
                                        @error('author')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="testimonial-role">{{ __('messages.role') }}</label>
                                        <input id="testimonial-role" name="role" class="form-control" maxlength="255" value="{{ old('role') }}">
                                        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="testimonial-company">{{ __('messages.testimonial_company') }}</label>
                                        <input id="testimonial-company" name="company" class="form-control" maxlength="255" value="{{ old('company') }}">
                                        @error('company')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <fieldset class="col-md-6 border-0 p-0 m-0">
                                        <legend class="form-label mb-2">{{ __('messages.testimonial_rating') }}</legend>
                                        <div class="testimonial-rating-options" role="radiogroup" aria-label="{{ __('messages.testimonial_rating') }}">
                                            @for($rating = 5; $rating >= 1; $rating--)
                                                <input class="testimonial-rating-input" id="testimonial-rating-{{ $rating }}" type="radio" name="rating" value="{{ $rating }}" aria-label="{{ $rating }} / 5" {{ (int) old('rating', 5) === $rating ? 'checked' : '' }} required>
                                                <label class="testimonial-rating-star" for="testimonial-rating-{{ $rating }}" aria-label="{{ $rating }} / 5" title="{{ $rating }} / 5">★</label>
                                            @endfor
                                        </div>
                                        @error('rating')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </fieldset>
                                    <div class="col-12">
                                        <label class="form-label" for="testimonial-content">{{ __('messages.testimonial_content') }}</label>
                                        <textarea id="testimonial-content" name="content" class="form-control" rows="4" minlength="10" maxlength="2000" required>{{ old('content') }}</textarea>
                                        @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">{{ __('messages.submit_testimonial') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

@if($errors->hasAny(['author', 'role', 'company', 'content', 'rating']))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const testimonialModal = document.getElementById('testimonial-modal');
            if (testimonialModal && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(testimonialModal).show();
            }
        });
    </script>
@endif