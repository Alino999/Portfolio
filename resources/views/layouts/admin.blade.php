<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') · {{ $brandSettings['site_name'] ?? 'Portfolio' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    <x-brand-theme :settings="$brandSettings" />
    @if(!empty($brandSettings['logo_path']))<link rel="icon" href="{{ asset('storage/'.$brandSettings['logo_path']) }}">@endif
</head>
<body class="admin-body">
    @php
        $adminBreadcrumb = match (true) {
            request()->routeIs('dashboard', 'admin.dashboard') => 'Tableau de bord',
            request()->routeIs('admin.profile.*') => 'Profil',
            request()->routeIs('admin.skills.*') => 'Compétences',
            request()->routeIs('admin.projects.*') => 'Projets',
            request()->routeIs('admin.experiences.*') => 'Expériences',
            request()->routeIs('admin.services.*') => 'Services',
            request()->routeIs('admin.portfolio-content.*') => 'Contenus complémentaires',
            request()->routeIs('admin.testimonials.*') => 'Témoignages',
            request()->routeIs('admin.home-slides.*') => 'Diaporama',
            request()->routeIs('admin.articles.*') => 'Blogue',
            request()->routeIs('admin.messages.*') => 'Messages',
            request()->routeIs('admin.resumes.*') => 'CV',
            request()->routeIs('admin.resume-template-categories.*') => 'Catégories de modèles',
            request()->routeIs('admin.resume-templates.*') => 'Modèles de CV',
            request()->routeIs('admin.settings*') => 'Paramètres',
            default => 'Administration',
        };
    @endphp
    <div class="admin-shell" id="admin-shell">
        <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="admin-sidebar" aria-labelledby="admin-sidebar-title">
            <div class="offcanvas-header admin-sidebar-header">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}" id="admin-sidebar-title">
                    @if(!empty($brandSettings['logo_path']))
                        <img class="admin-brand-logo" src="{{ asset('storage/'.$brandSettings['logo_path']) }}" alt="{{ $brandSettings['site_name'] ?? 'Portfolio' }}">
                        <span><small>Administration</small></span>
                    @else
                        <span class="admin-brand-mark" aria-hidden="true">AG</span>
                        <span><strong>{{ $brandSettings['site_name'] ?? 'Portfolio' }}</strong><small>Administration</small></span>
                    @endif
                </a>
                <button type="button" class="btn-close d-lg-none" id="admin-sidebar-close" aria-label="Fermer le menu"></button>
            </div>
            <div class="offcanvas-body admin-sidebar-body">
                <nav class="admin-nav" aria-label="Navigation d’administration">
                    <span class="admin-nav-heading">Espace</span>
                    <a class="admin-nav-link {{ request()->routeIs('dashboard', 'admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" title="Tableau de bord" aria-label="Tableau de bord" @if(request()->routeIs('dashboard', 'admin.dashboard')) aria-current="page" @endif><i class="bi bi-grid-1x2" aria-hidden="true"></i><span class="admin-nav-label">Tableau de bord</span></a>

                    <span class="admin-nav-heading">Portfolio</span>
                    <a class="admin-nav-link {{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}" href="{{ route('admin.profile.edit') }}" title="Profil" aria-label="Profil" @if(request()->routeIs('admin.profile.*')) aria-current="page" @endif><i class="bi bi-person-vcard" aria-hidden="true"></i><span class="admin-nav-label">Profil</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.skills.*') ? 'is-active' : '' }}" href="{{ route('admin.skills.index') }}" title="Compétences" aria-label="Compétences" @if(request()->routeIs('admin.skills.*')) aria-current="page" @endif><i class="bi bi-lightning-charge" aria-hidden="true"></i><span class="admin-nav-label">Compétences</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.projects.*') ? 'is-active' : '' }}" href="{{ route('admin.projects.index') }}" title="Projets" aria-label="Projets" @if(request()->routeIs('admin.projects.*')) aria-current="page" @endif><i class="bi bi-briefcase" aria-hidden="true"></i><span class="admin-nav-label">Projets</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.experiences.*') ? 'is-active' : '' }}" href="{{ route('admin.experiences.index') }}" title="Expériences" aria-label="Expériences" @if(request()->routeIs('admin.experiences.*')) aria-current="page" @endif><i class="bi bi-clock-history" aria-hidden="true"></i><span class="admin-nav-label">Expériences</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}" href="{{ route('admin.services.index') }}" title="Services" aria-label="Services" @if(request()->routeIs('admin.services.*')) aria-current="page" @endif><i class="bi bi-boxes" aria-hidden="true"></i><span class="admin-nav-label">Services</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.portfolio-content.*') ? 'is-active' : '' }}" href="{{ route('admin.portfolio-content.index', 'educations') }}" title="Contenus complémentaires" aria-label="Contenus complémentaires" @if(request()->routeIs('admin.portfolio-content.*')) aria-current="page" @endif><i class="bi bi-collection" aria-hidden="true"></i><span class="admin-nav-label">Autres contenus</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.testimonials.*') ? 'is-active' : '' }}" href="{{ route('admin.testimonials.index') }}" title="Témoignages" aria-label="Témoignages" @if(request()->routeIs('admin.testimonials.*')) aria-current="page" @endif><i class="bi bi-chat-quote" aria-hidden="true"></i><span class="admin-nav-label">Témoignages</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.home-slides.*') ? 'is-active' : '' }}" href="{{ route('admin.home-slides.index') }}" title="Diaporama" aria-label="Diaporama" @if(request()->routeIs('admin.home-slides.*')) aria-current="page" @endif><i class="bi bi-images" aria-hidden="true"></i><span class="admin-nav-label">Diaporama</span></a>

                    <span class="admin-nav-heading">Communication</span>
                    <a class="admin-nav-link {{ request()->routeIs('admin.articles.*') ? 'is-active' : '' }}" href="{{ route('admin.articles.index') }}" title="Blogue" aria-label="Blogue" @if(request()->routeIs('admin.articles.*')) aria-current="page" @endif><i class="bi bi-journal-text" aria-hidden="true"></i><span class="admin-nav-label">Blogue</span></a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.messages.*') ? 'is-active' : '' }}" href="{{ route('admin.messages.index') }}" title="Messages" aria-label="Messages" @if(request()->routeIs('admin.messages.*')) aria-current="page" @endif><i class="bi bi-envelope" aria-hidden="true"></i><span class="admin-nav-label">Messages</span></a>

                    <span class="admin-nav-heading">Documents</span>
                    <a class="admin-nav-link {{ request()->routeIs('admin.resumes.*') ? 'is-active' : '' }}" href="{{ route('admin.resumes.index') }}" title="Mes CV" aria-label="Mes CV" @if(request()->routeIs('admin.resumes.*')) aria-current="page" @endif><i class="bi bi-file-earmark-person" aria-hidden="true"></i><span class="admin-nav-label">Mes CV</span></a>
                    @role('super-admin')
                        <a class="admin-nav-link {{ request()->routeIs('admin.resume-templates.*', 'admin.resume-template-categories.*') ? 'is-active' : '' }}" href="{{ route('admin.resume-templates.index') }}" title="Modèles de CV" aria-label="Modèles de CV" @if(request()->routeIs('admin.resume-templates.*', 'admin.resume-template-categories.*')) aria-current="page" @endif><i class="bi bi-file-earmark-richtext" aria-hidden="true"></i><span class="admin-nav-label">Modèles de CV</span></a>
                    @endrole

                    <span class="admin-nav-heading">Configuration</span>
                    <a class="admin-nav-link {{ request()->routeIs('admin.settings*') ? 'is-active' : '' }}" href="{{ route('admin.settings') }}" title="Paramètres" aria-label="Paramètres" @if(request()->routeIs('admin.settings*')) aria-current="page" @endif><i class="bi bi-gear" aria-hidden="true"></i><span class="admin-nav-label">Paramètres</span></a>
                </nav>
            </div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar">
                <button class="btn btn-outline-secondary admin-sidebar-toggle" type="button" id="admin-sidebar-toggle" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="admin-sidebar"><i class="bi bi-list" aria-hidden="true"></i></button>
                <nav class="admin-breadcrumbs" aria-label="Fil d’Ariane">
                    @if(!request()->routeIs('dashboard', 'admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    @endif
                    <span aria-current="page">{{ $adminBreadcrumb }}</span>
                </nav>
                <div class="admin-topbar-actions">
                    <a class="admin-topbar-icon" href="{{ route('admin.messages.index') }}" aria-label="Messages" title="Messages"><i class="bi bi-envelope" aria-hidden="true"></i></a>
                    <label class="visually-hidden" for="admin-theme-select">Thème de l’interface</label>
                    <select class="form-select form-select-sm admin-theme-select" id="admin-theme-select" aria-label="Thème de l’interface">
                        <option value="auto">Auto</option>
                        <option value="light">Clair</option>
                        <option value="dark">Sombre</option>
                    </select>
                    <x-admin.icon-action :href="route('home')" size="sm" variant="outline-secondary" icon="bi-box-arrow-up-right" label="Voir le site public" class="admin-public-link" />
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm admin-account-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu du compte de {{ auth()->user()->name }}">
                            <i class="bi bi-person-circle" aria-hidden="true"></i><span>{{ auth()->user()->name }}</span><i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end admin-account-menu">
                            <div class="admin-account-heading">
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ auth()->user()->email }}</span>
                                <small>@role('super-admin')Super-admin @else Administrateur @endrole</small>
                            </div>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}" aria-label="Mon compte" title="Mon compte"><i class="bi bi-person-gear" aria-hidden="true"></i><span class="visually-hidden">Mon compte</span></a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" aria-label="Déconnexion" title="Déconnexion"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span class="visually-hidden">Déconnexion</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main id="admin-content" class="admin-content" tabindex="-1">
                @if($errors->any())
                    <div class="container pt-3">
                        <div class="alert alert-danger" role="alert" aria-labelledby="admin-validation-title">
                            <h2 id="admin-validation-title" class="h6 mb-2">Vérifiez les informations saisies</h2>
                            <ul class="mb-0">
                                @foreach($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('admin-sidebar-close')?.addEventListener('click', () => {
            bootstrap.Offcanvas.getInstance(document.getElementById('admin-sidebar'))?.hide();
        });
    </script>
    @stack('scripts')
</body>
</html>
