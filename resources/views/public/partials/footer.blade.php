<footer class="portfolio-footer">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        @if(!empty($settings['logo_path']))
            <div class="portfolio-footer-brand">
                <img class="portfolio-footer-logo" src="{{ asset('storage/'.$settings['logo_path']) }}" alt="">
                <span class="fw-semibold">{{ $settings['site_name'] ?? trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? '')) }}</span>
            </div>
        @else
            <span class="fw-semibold">{{ $settings['site_name'] ?? trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? '')) }}</span>
        @endif
        <span class="small">{{ __('messages.footer_text') }}</span>
        <a href="#top" class="small">{{ __('messages.back_to_top') }} ↑</a>
    </div>
</footer>