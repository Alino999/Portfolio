<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $brandSettings['site_name'] ?? config('app.name', 'Portfolio') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <x-brand-theme :settings="$brandSettings" />
        @if(!empty($brandSettings['logo_path']))<link rel="icon" href="{{ asset('storage/'.$brandSettings['logo_path']) }}">@endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative min-h-screen overflow-hidden bg-slate-950">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(21,118,111,0.32),_transparent_35%),radial-gradient(circle_at_bottom_right,_rgba(216,120,95,0.28),_transparent_28%)]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(15,23,42,0.96),rgba(15,23,42,0.86))]"></div>

            <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
                <div class="w-full max-w-5xl overflow-hidden rounded-[28px] border border-white/10 bg-white/80 shadow-[0_30px_80px_rgba(15,23,42,0.45)] backdrop-blur-xl">
                    <div class="grid lg:grid-cols-[1.1fr_0.9fr]">
                        <div class="hidden lg:flex relative flex-col justify-between bg-slate-900 p-10 text-white">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.12),transparent_28%),linear-gradient(135deg,rgba(21,118,111,0.5),rgba(15,23,42,0.9))]"></div>
                            <div class="relative z-10">
                                <div class="mb-8 flex items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/10 backdrop-blur-sm">
                                        <a href="/" class="inline-flex">
                                            <x-application-logo :logo-path="$brandSettings['logo_path'] ?? null" :site-name="$brandSettings['site_name'] ?? 'Portfolio'" class="h-10 w-10 object-contain" />
                                        </a>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.28em] text-slate-300">Espace privé</p>
                                        <h1 class="text-2xl font-semibold text-white">{{ $brandSettings['site_name'] ?? config('app.name', 'Portfolio') }}</h1>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <p class="text-sm uppercase tracking-[0.3em] text-[var(--brand-accent)]">Administration</p>
                                    <h2 class="max-w-sm text-4xl font-bold leading-tight text-white">Gérez votre présence digitale avec confiance.</h2>
                                    <p class="max-w-md text-base text-slate-200">Publiez vos contenus, gérez vos projets, pilotez votre portfolio et contrôlez chaque détail de votre image en ligne.</p>
                                </div>
                            </div>

                            <div class="relative z-10 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                                    <div class="text-2xl font-bold text-white">Portfolio</div>
                                    <div class="mt-1 text-sm text-slate-300">Contenus & projets</div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                                    <div class="text-2xl font-bold text-white">Blog</div>
                                    <div class="mt-1 text-sm text-slate-300">Actualités & publications</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
                            <div class="mb-8 flex items-center justify-center lg:hidden">
                                <a href="/" class="flex items-center gap-3 rounded-full border border-slate-200 bg-slate-50 px-3 py-2">
                                    <x-application-logo :logo-path="$brandSettings['logo_path'] ?? null" :site-name="$brandSettings['site_name'] ?? 'Portfolio'" class="h-8 w-8 object-contain" />
                                    <span class="text-sm font-semibold text-slate-800">{{ $brandSettings['site_name'] ?? config('app.name', 'Portfolio') }}</span>
                                </a>
                            </div>

                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
