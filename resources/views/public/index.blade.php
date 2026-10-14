<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $pageDescription = App\Support\LocaleHelper::text(['fr' => $profile?->summary ?? 'Portfolio professionnel', 'en' => $profile?->summary_en ?? 'Professional portfolio']);
        $pageTitle = $settings['site_name'] ?? trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? ''));
    @endphp
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ route('home') }}">
    <meta name="twitter:card" content="{{ $homepageSlides->isNotEmpty() ? 'summary_large_image' : 'summary' }}">
    @if($homepageSlides->isNotEmpty())<meta property="og:image" content="{{ url('/storage/'.ltrim($homepageSlides->first()->file_path, '/')) }}">@endif
    <title>{{ $settings['site_name'] ?? 'Portfolio' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/portfolio.css', 'resources/js/portfolio.js'])
    <x-brand-theme :settings="$settings" />
    @if(!empty($settings['logo_path']))<link rel="icon" href="{{ asset('storage/'.$settings['logo_path']) }}">@endif
</head>
<body class="portfolio-page" id="top">
    @include('public.partials.navigation')
    @include('public.partials.hero')

    <main id="main-content">
        @include('public.partials.profile-sections')

        @include('public.partials.work-sections')

        @include('public.partials.publications-sections')
        @include('public.partials.contact')
    </main>

    @include('public.partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
