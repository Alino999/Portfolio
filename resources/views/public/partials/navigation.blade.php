@php
    $brandInitials = mb_strtoupper(mb_substr($profile?->last_name ?? '', 0, 1).mb_substr($profile?->first_name ?? '', 0, 1));
    $profileEducations = $profile?->educations?->where('published', true) ?? collect();
    $profileAchievements = $profile?->achievements?->where('published', true) ?? collect();
    $profileReferences = $profile?->professionalReferences?->where('published', true) ?? collect();
    $activeSocialLinks = $profile?->socialLinks?->where('is_active', true) ?? collect();
    $hasOtherSections = $technologies->isNotEmpty()
        || ($sections->contains('key', 'services') && $services->isNotEmpty())
        || ($sections->contains('key', 'certifications') && $certifications->isNotEmpty())
        || $languages->isNotEmpty()
        || $profileAchievements->isNotEmpty()
        || $profileReferences->isNotEmpty()
        || $activeSocialLinks->isNotEmpty();
@endphp
<nav class="navbar navbar-expand-lg portfolio-nav" data-site-nav>
    <div class="container py-2">
        <a class="navbar-brand portfolio-brand" href="{{ route('home') }}">
            @if(!empty($settings['logo_path']))
                <img class="portfolio-brand-logo" src="{{ asset('storage/'.$settings['logo_path']) }}" alt="">
                <span class="portfolio-brand-name">{{ $settings['site_name'] ?? trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? '')) }}</span>
            @else
                @if($brandInitials !== '')<span class="portfolio-brand-mark">{{ $brandInitials }}</span>@endif
                {{ $settings['site_name'] ?? trim(($profile?->first_name ?? '').' '.($profile?->last_name ?? '')) }}
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portfolio-navigation" aria-controls="portfolio-navigation" aria-expanded="false" aria-label="{{ app()->getLocale() === 'fr' ? 'Ouvrir le menu' : 'Open menu' }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="portfolio-navigation">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                @if($sections->contains('key', 'about'))<li class="nav-item"><a class="nav-link" href="#about">{{ __('messages.about') }}</a></li>@endif
                @if($sections->contains('key', 'skills') && $skills->isNotEmpty())<li class="nav-item"><a class="nav-link" href="#skills">{{ __('messages.skills') }}</a></li>@endif
                @if($sections->contains('key', 'experience') && $experiences->isNotEmpty())<li class="nav-item"><a class="nav-link" href="#experience">{{ __('messages.experience') }}</a></li>@endif
                @if($profileEducations->isNotEmpty())<li class="nav-item"><a class="nav-link" href="#education">{{ __('messages.education') }}</a></li>@endif
                @if($sections->contains('key', 'projects') && $projects->isNotEmpty())<li class="nav-item"><a class="nav-link" href="#projects">{{ __('messages.projects') }}</a></li>@endif
                @if($hasOtherSections)
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ app()->getLocale() === 'fr' ? 'Autres' : 'More' }}</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($technologies->isNotEmpty())<li><a class="dropdown-item" href="#technologies">{{ __('messages.technologies') }}</a></li>@endif
                            @if($sections->contains('key', 'services') && $services->isNotEmpty())<li><a class="dropdown-item" href="#services">{{ __('messages.services') }}</a></li>@endif
                            @if($sections->contains('key', 'certifications') && $certifications->isNotEmpty())<li><a class="dropdown-item" href="#certifications">{{ __('messages.certifications') }}</a></li>@endif
                            @if($languages->isNotEmpty())<li><a class="dropdown-item" href="#languages">{{ __('messages.languages') }}</a></li>@endif
                            @if($profileAchievements->isNotEmpty())<li><a class="dropdown-item" href="#achievements">{{ app()->getLocale() === 'fr' ? 'Réalisations' : 'Achievements' }}</a></li>@endif
                            @if($profileReferences->isNotEmpty())<li><a class="dropdown-item" href="#references">{{ app()->getLocale() === 'fr' ? 'Références' : 'References' }}</a></li>@endif
                            @if($activeSocialLinks->isNotEmpty())<li><a class="dropdown-item" href="#social-links">{{ app()->getLocale() === 'fr' ? 'Réseaux sociaux' : 'Social networks' }}</a></li>@endif
                        </ul>
                    </li>
                @endif
                @if($articles->isNotEmpty())<li class="nav-item"><a class="nav-link" href="#blog">{{ __('messages.blog') }}</a></li>@endif
                <li class="nav-item"><a class="nav-link" href="#contact">{{ __('messages.contact') }}</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 ms-lg-3 mt-3 mt-lg-0">
                <a href="{{ route('locale.switch', ['locale' => 'fr']) }}" class="btn btn-sm {{ app()->getLocale() === 'fr' ? 'btn-primary' : 'btn-outline-secondary' }}" @if(app()->getLocale() === 'fr') aria-current="page" @endif>FR</a>
                <a href="{{ route('locale.switch', ['locale' => 'en']) }}" class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-outline-secondary' }}" @if(app()->getLocale() === 'en') aria-current="page" @endif>EN</a>
                <button class="btn btn-sm btn-outline-secondary portfolio-theme-toggle" type="button" data-theme-toggle data-dark-label="{{ app()->getLocale() === 'fr' ? 'Activer le thème sombre' : 'Enable dark theme' }}" data-light-label="{{ app()->getLocale() === 'fr' ? 'Activer le thème clair' : 'Enable light theme' }}" aria-label="{{ app()->getLocale() === 'fr' ? 'Activer le thème sombre' : 'Enable dark theme' }}" aria-pressed="false"><span data-theme-icon aria-hidden="true">☾</span></button>
            </div>
        </div>
    </div>
</nav>