@extends('layouts.admin')

@section('content')
<div class="container-fluid admin-dashboard py-4">
    <header class="admin-dashboard-heading mb-4">
        <div>
            <span class="admin-profile-kicker">Espace de gestion</span>
            <h1 class="h3 mb-1">Bonjour, {{ auth()->user()->name }}</h1>
            <p class="text-muted mb-0">Voici l’activité récente de votre portfolio.</p>
        </div>
    </header>

    @php
        $dashboardCards = [
            ['label' => 'Projets', 'value' => $stats['projects'], 'icon' => 'bi-briefcase', 'route' => 'admin.projects.index', 'action' => 'Voir les projets'],
            ['label' => 'Expériences', 'value' => $stats['experiences'], 'icon' => 'bi-clock-history', 'route' => 'admin.experiences.index', 'action' => 'Voir les expériences'],
            ['label' => 'Formations', 'value' => $stats['educations'], 'icon' => 'bi-mortarboard', 'route' => null],
            ['label' => 'Compétences', 'value' => $stats['skills'], 'icon' => 'bi-lightning-charge', 'route' => 'admin.skills.index', 'action' => 'Voir les compétences'],
            ['label' => 'Certifications', 'value' => $stats['certifications'], 'icon' => 'bi-patch-check', 'route' => null],
            ['label' => 'CV', 'value' => $stats['resumes'], 'icon' => 'bi-file-earmark-person', 'route' => 'admin.resumes.index', 'action' => 'Voir mes CV'],
            ['label' => 'Messages', 'value' => $stats['messages'], 'icon' => 'bi-envelope', 'route' => 'admin.messages.index', 'action' => 'Voir les messages'],
            ['label' => 'Articles', 'value' => $stats['articles'], 'icon' => 'bi-journal-text', 'route' => 'admin.articles.index', 'action' => 'Voir les articles'],
        ];
    @endphp

    <section class="row g-3 mb-4" aria-label="Indicateurs du portfolio">
        @foreach($dashboardCards as $card)
            <div class="col-6 col-xl-3">
                <article class="admin-kpi-card h-100">
                    <div class="admin-kpi-topline"><span>{{ $card['label'] }}</span><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></div>
                    <div class="admin-kpi-value">{{ $card['value'] }}</div>
                    @if($card['route'])
                        <x-admin.icon-action :href="route($card['route'])" icon="bi-arrow-up-right" variant="outline-secondary" :label="$card['action']" class="admin-kpi-link" />
                    @else
                        <span class="admin-kpi-note">Enregistré{{ $card['value'] === 1 ? '' : 's' }}</span>
                    @endif
                </article>
            </div>
        @endforeach
    </section>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card h-100" aria-labelledby="recent-activity-title">
                <div class="card-header d-flex align-items-center justify-content-between gap-3">
                    <h2 class="h6 mb-0" id="recent-activity-title">Activité récente</h2>
                    <span class="small text-muted">Données réelles</span>
                </div>
                <div class="list-group list-group-flush admin-activity-list">
                    @forelse($recentActivity as $activity)
                        <a href="{{ $activity['url'] }}" class="list-group-item list-group-item-action admin-activity-item">
                            <span class="admin-activity-icon"><i class="bi {{ $activity['icon'] }}" aria-hidden="true"></i></span>
                            <span class="admin-activity-copy">
                                <strong>{{ $activity['type'] }}</strong>
                                <span>{{ $activity['title'] }}</span>
                                <small>{{ $activity['date']?->diffForHumans() }} · {{ $activity['date']?->format('d/m/Y H:i') }}</small>
                            </span>
                            <i class="bi bi-arrow-up-right admin-activity-arrow" aria-hidden="true"></i>
                        </a>
                    @empty
                        <p class="list-group-item mb-0 py-4 text-muted">Aucune activité enregistrée pour le moment.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="card h-100" aria-labelledby="quick-actions-title">
                <div class="card-header"><h2 class="h6 mb-0" id="quick-actions-title">Actions rapides</h2></div>
                <div class="admin-quick-actions">
                    <x-admin.icon-action :href="route('admin.projects.index')" icon="bi-folder-plus" label="Ajouter un projet" class="admin-quick-action-icon" />
                    <x-admin.icon-action :href="route('admin.experiences.index')" icon="bi-person-workspace" label="Ajouter une expérience" class="admin-quick-action-icon" />
                    <x-admin.icon-action :href="route('admin.skills.index')" icon="bi-lightning-charge" label="Ajouter une compétence" class="admin-quick-action-icon" />
                    <x-admin.icon-action :href="route('admin.resumes.index')" icon="bi-file-earmark-plus" label="Créer un CV" class="admin-quick-action-icon" />
                    <x-admin.icon-action :href="route('admin.articles.index')" icon="bi-journal-plus" label="Gérer les articles" class="admin-quick-action-icon" />
                </div>
            </section>
            </div>
        </div>
    </div>
</div>
@endsection
