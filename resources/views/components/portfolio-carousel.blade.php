@props(['label'])

@php $isFrench = app()->getLocale() === 'fr'; @endphp
<div class="portfolio-carousel" data-portfolio-carousel aria-label="{{ $label }}">
    <div class="portfolio-carousel-track" data-portfolio-track tabindex="0" aria-label="{{ $label }}">
        {{ $slot }}
    </div>
    <div class="portfolio-carousel-controls" aria-label="{{ $isFrench ? 'Navigation' : 'Navigation' }} {{ $label }}">
        <button type="button" class="btn btn-outline-primary" data-carousel-prev aria-label="{{ $isFrench ? 'Élément précédent' : 'Previous item' }}"><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-outline-primary" data-carousel-toggle aria-label="{{ $isFrench ? 'Mettre en pause le défilement automatique' : 'Pause automatic scrolling' }}" data-pause-label="{{ $isFrench ? 'Mettre en pause le défilement automatique' : 'Pause automatic scrolling' }}" data-resume-label="{{ $isFrench ? 'Reprendre le défilement automatique' : 'Resume automatic scrolling' }}" aria-pressed="false" title="{{ $isFrench ? 'Pause ou reprise du défilement automatique' : 'Pause or resume automatic scrolling' }}"><i class="bi bi-pause-fill" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-outline-primary" data-carousel-next aria-label="{{ $isFrench ? 'Élément suivant' : 'Next item' }}"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
    </div>
</div>