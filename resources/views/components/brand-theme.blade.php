@props(['settings'])

@php
    $primaryColor = $settings['primary_color'] ?? '#15766f';
    $accentColor = $settings['accent_color'] ?? '#d8785f';
    $primaryColor = is_string($primaryColor) && preg_match('/\A#[0-9A-Fa-f]{6}\z/', $primaryColor) ? $primaryColor : '#15766f';
    $accentColor = is_string($accentColor) && preg_match('/\A#[0-9A-Fa-f]{6}\z/', $accentColor) ? $accentColor : '#d8785f';
@endphp

<style>
    :root {
        --brand-primary: {{ $primaryColor }};
        --brand-accent: {{ $accentColor }};
    }
</style>