@props([
    'icon',
    'label',
    'variant' => 'secondary',
    'size' => null,
    'type' => 'button',
    'href' => null,
])

@php
    $actionClasses = collect([
        'btn',
        'btn-'.$variant,
        $size ? 'btn-'.$size : null,
        'admin-icon-action',
        $attributes->get('class'),
    ])->filter()->implode(' ');
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $actionClasses }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->except('class') }}>
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
        <span class="visually-hidden">{{ $label }}</span>
    </a>
@else
    <button type="{{ $type }}" class="{{ $actionClasses }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->except('class') }}>
        <i class="bi {{ $icon }}" aria-hidden="true"></i>
        <span class="visually-hidden">{{ $label }}</span>
    </button>
@endif