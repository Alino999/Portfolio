<section class="portfolio-section" id="contact">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5" data-reveal>
                <div class="portfolio-section-heading">
                    <span class="portfolio-eyebrow">{{ __('messages.contact') }}</span>
                    <h2>{{ __('messages.contact_heading') }}</h2>
                    <p>{{ __('messages.contact_intro') }}</p>
                </div>
                <div class="d-grid gap-3">
                    @if($profile?->email)<a href="mailto:{{ $profile->email }}" class="portfolio-contact-link"><span>{{ __('messages.email') }}</span><strong>{{ $profile->email }}</strong></a>@endif
                    @if($profile?->phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $profile->phone) }}" class="portfolio-contact-link"><span>{{ __('messages.phone') }}</span><strong>{{ $profile->phone }}</strong></a>@endif
                    @if($profile?->location)<div class="portfolio-contact-link"><span>{{ __('messages.location') }}</span><strong>{{ $profile->location }}</strong></div>@endif
                </div>
            </div>
            <div class="col-lg-7" data-reveal>
                @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('public.contact') }}" class="portfolio-contact-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="contact-name">{{ __('messages.name') }}</label><input id="contact-name" type="text" name="name" class="form-control" value="{{ old('name') }}" autocomplete="name" required></div>
                        <div class="col-md-6"><label class="form-label" for="contact-email">{{ __('messages.email') }}</label><input id="contact-email" type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" required></div>
                        <div class="col-md-6"><label class="form-label" for="contact-phone">{{ __('messages.phone') }}</label><input id="contact-phone" type="tel" name="phone" class="form-control" value="{{ old('phone') }}" autocomplete="tel"></div>
                        <div class="col-md-6"><label class="form-label" for="contact-subject">{{ __('messages.subject') }}</label><input id="contact-subject" type="text" name="subject" class="form-control" value="{{ old('subject') }}" required></div>
                        <div class="col-12"><label class="form-label" for="contact-message">{{ __('messages.message') }}</label><textarea id="contact-message" name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea></div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">{{ __('messages.send') }} <span aria-hidden="true">→</span></button>
                </form>
            </div>
        </div>
    </div>
</section>