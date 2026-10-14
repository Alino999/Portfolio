@extends('layouts.admin')

@section('content')
<div class="container-fluid px-lg-4 py-4">
    <style>
        .template-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(255px, 1fr)); }
        .template-card { background: #fff; border: 1px solid #dbe2e7; border-radius: 6px; height: 100%; overflow: hidden; }
        .template-card.is-inactive { opacity: .65; }
        .template-thumb { background: #edf1f3; height: 200px; overflow: hidden; padding: 17px; }
        .template-paper { background: white; box-shadow: 0 3px 12px #162b3d22; height: 100%; margin: auto; max-width: 145px; overflow: hidden; padding: 13px 11px; position: relative; }
        .template-mark { background: var(--template-primary, #17324d); height: 9px; margin-bottom: 6px; width: 62%; }
        .template-submark { background: var(--template-secondary, #0f766e); height: 4px; margin-bottom: 10px; width: 45%; }
        .template-heading { border-bottom: 2px solid var(--template-secondary, #0f766e); height: 9px; margin: 8px 0 6px; width: 46%; }
        .template-line { background: #d8e0e3; height: 3px; margin: 4px 0; width: 90%; }
        .template-line.short { width: 59%; }
        .layout-minimal .template-paper { padding: 17px; text-align: center; }
        .layout-minimal .template-mark, .layout-minimal .template-submark, .layout-minimal .template-heading { margin-left: auto; margin-right: auto; }
        .layout-corporate .template-paper { border-top: 7px solid var(--template-primary); }
        .layout-international .template-paper { display: grid; grid-template-columns: 1fr 1fr; gap: 0 9px; }
        .layout-international .template-mark, .layout-international .template-submark { grid-column: 1 / -1; }
        .layout-ats-clean .template-paper { max-width: 128px; padding: 16px 12px; }
        .layout-tech .template-paper { border-left: 8px solid var(--template-primary); }
        .layout-cybersecurity .template-paper { background: #182936; }
        .layout-cybersecurity .template-mark { background: #f1f5f7; }
        .layout-consultant .template-paper { border-top: 22px solid var(--template-primary); }
        .layout-consultant .template-mark { margin-top: -17px; position: relative; }
        .layout-academic .template-paper { font-family: Georgia, serif; }
        .layout-academic .template-mark { height: 5px; margin-left: auto; margin-right: auto; }
        .layout-creative-professional .template-paper { border-left: 22px solid var(--template-secondary); }
        .layout-executive-premium .template-paper { border: 1px solid var(--template-secondary); padding: 18px 12px; }
        .layout-executive-premium .template-mark, .layout-executive-premium .template-submark { margin-left: auto; margin-right: auto; }
        .layout-modern-executive .template-paper { border-left: 5px solid var(--template-secondary); }
        .layout-classic-professional .template-paper { border-top: 2px solid var(--template-primary); }
        .layout-editorial-nordic .template-paper { border-top: 4px solid var(--template-secondary); font-family: Georgia, serif; }
        .layout-portfolio-showcase .template-paper { border-left: 7px solid var(--template-secondary); padding-left: 8px; }
        .layout-bold-header .template-paper { border-top: 16px solid var(--template-primary); padding-top: 8px; }
        .layout-graduate-focus .template-paper { border-bottom: 4px solid var(--template-secondary); font-family: Georgia, serif; text-align: center; }
        .layout-graduate-focus .template-mark, .layout-graduate-focus .template-submark, .layout-graduate-focus .template-heading { margin-left: auto; margin-right: auto; }
        .layout-portfolio-sidebar .template-paper { background: var(--template-primary); border-left: 36px solid color-mix(in srgb, var(--template-primary) 72%, #fff); padding-left: 7px; }
        .layout-portfolio-sidebar .template-paper::before { display: block; width: 26px; height: 26px; margin: 0 auto 8px; border-radius: 50%; background: var(--template-secondary); content: ""; }
        .layout-portfolio-sidebar .template-mark { background: #fff; }
        .layout-portfolio-sidebar .template-submark { background: var(--template-secondary); }
        .layout-portfolio-sidebar .template-heading { border-color: var(--template-secondary); }
        .layout-warm-academic .template-paper { border: 1px solid #ead8d2; background: #fffaf2; font-family: Georgia, serif; }
        .layout-warm-academic .template-paper::before { position: absolute; top: 8px; left: 9px; display: block; width: 25px; height: 25px; color: #fff; background: var(--template-secondary); content: "+"; font: bold 20px/25px Arial, sans-serif; text-align: center; }
        .layout-warm-academic .template-mark { margin-left: 30px; background: var(--template-primary); }
        .layout-warm-academic .template-submark { background: var(--template-secondary); }
        .layout-warm-academic .template-heading { border-color: var(--template-secondary); }
        .layout-portfolio-sidebar .template-paper { background: var(--template-primary); border-left: 34px solid color-mix(in srgb, var(--template-primary) 72%, #fff); padding-left: 7px; }
        .layout-portfolio-sidebar .template-paper::before { background: var(--template-secondary); border-radius: 50%; content: ""; display: block; height: 25px; margin: 0 auto 8px; width: 25px; }
        .layout-portfolio-sidebar .template-mark { background: #fff; }
        .layout-portfolio-sidebar .template-submark { background: var(--template-secondary); }
        .layout-portfolio-sidebar .template-heading { border-color: var(--template-secondary); }
        .layout-warm-academic .template-paper { background: #fffaf2; border: 1px solid #ead8d2; font-family: Georgia, serif; }
        .layout-warm-academic .template-paper::before { background: var(--template-secondary); content: "+"; color: #fff; display: block; font: bold 20px/25px Arial, sans-serif; height: 25px; left: 9px; position: absolute; text-align: center; top: 8px; width: 25px; }
        .layout-warm-academic .template-mark { background: var(--template-primary); margin-left: 30px; }
        .layout-warm-academic .template-submark { background: var(--template-secondary); }
        .layout-warm-academic .template-heading { border-color: var(--template-secondary); }
        .layout-portfolio-sidebar .template-paper { background: var(--template-primary); border-left: 5px solid var(--template-secondary); padding-left: 9px; }
        .layout-portfolio-sidebar .template-mark { background: #fff; }
        .layout-portfolio-sidebar .template-submark { background: var(--template-secondary); }
        .layout-portfolio-sidebar .template-heading { border-color: var(--template-secondary); }
        .layout-portfolio-sidebar .template-line { background: #d4dfda; }
        .layout-warm-academic .template-paper { background: #fffaf2; border: 1px solid #ead8d2; font-family: Georgia, serif; }
        .layout-warm-academic .template-mark { background: var(--template-primary); }
        .layout-warm-academic .template-submark { background: var(--template-secondary); }
        .layout-warm-academic .template-heading { border-color: var(--template-secondary); }
        .ats-note { font-size: 12px; }
        @media (prefers-reduced-motion: reduce) { .template-card { transition: none; } }
    </style>

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Modèles de CV</h1>
            <p class="text-muted mb-0">{{ $templates->where('is_active', true)->count() }} modèles disponibles</p>
        </div>
        <div class="d-flex gap-2">
            <x-admin.icon-action :href="route('admin.resume-template-categories.index')" variant="outline-secondary" icon="bi-tags" label="Gérer les catégories" />
            <x-admin.icon-action type="button" variant="primary" icon="bi-plus-lg" label="Nouveau modèle" data-bs-toggle="collapse" data-bs-target="#newTemplateForm" aria-expanded="false" />
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif

    <div class="collapse mb-4" id="newTemplateForm">
        <div class="card card-body">
            <h2 class="h5 mb-3">Créer un modèle</h2>
            @include('admin.resume-templates._form', ['template' => null, 'action' => route('admin.resume-templates.store'), 'method' => 'POST'])
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <a href="{{ route('admin.resume-templates.index') }}" class="btn btn-sm {{ request('category') ? 'btn-outline-secondary' : 'btn-secondary' }}">Tous</a>
        @foreach($categories as $category)
            <a href="{{ route('admin.resume-templates.index', ['category' => $category->slug]) }}" class="btn btn-sm {{ request('category') === $category->slug ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $category->name }}</a>
        @endforeach
        <label class="form-check ms-auto mb-0">
            <input class="form-check-input" type="checkbox" id="showInactiveTemplates" {{ request()->boolean('inactive') ? 'checked' : '' }}>
            <span class="form-check-label">Afficher les inactifs</span>
        </label>
    </div>

    <x-admin.bulk-actions type="resume-templates" :actions="['publish' => 'Activer', 'unpublish' => 'Désactiver', 'delete' => 'Supprimer']" />

    <div class="template-grid">
        @forelse($templates as $template)
            @php
                $palette = $template->available_colors[0] ?? ['primary' => '#17324d', 'secondary' => '#0f766e'];
            @endphp
            <article class="template-card {{ $template->is_active ? '' : 'is-inactive' }}">
                <div class="template-thumb layout-{{ $template->layout }}" style="--template-primary: {{ $palette['primary'] ?? '#17324d' }}; --template-secondary: {{ $palette['secondary'] ?? '#0f766e' }};">
                    <div class="template-paper" aria-hidden="true">
                        <div class="template-mark"></div>
                        <div class="template-submark"></div>
                        <div class="template-heading"></div>
                        <div class="template-line"></div><div class="template-line short"></div>
                        <div class="template-heading"></div>
                        <div class="template-line"></div><div class="template-line short"></div>
                        <div class="template-heading"></div>
                        <div class="template-line"></div><div class="template-line short"></div>
                    </div>
                </div>
                <div class="p-3">
                    <input type="checkbox" class="form-check-input mb-2" name="ids[]" value="{{ $template->id }}" form="bulk-action-resume-templates" data-bulk-item aria-label="Sélectionner le modèle {{ $template->name }}">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <h2 class="h6 mb-1">{{ $template->name }}</h2>
                            <span class="small text-muted">{{ $template->category?->name ?? 'Sans catégorie' }} · {{ $template->style ?? 'Professionnel' }}</span>
                        </div>
                        <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $template->is_active ? 'Actif' : 'Inactif' }}</span>
                    </div>
                    <p class="small text-muted my-2">{{ $template->description }}</p>
                    <div class="d-flex align-items-center flex-wrap gap-2 small mb-3">
                        <span class="badge {{ $template->ats_friendly ? 'text-bg-success' : 'text-bg-light border text-dark' }}">{{ $template->ats_friendly ? 'ATS Friendly' : 'Graphique' }}</span>
                        <span>{{ strtoupper(implode(' · ', $template->languages ?? [])) }}</span>
                        <span>{{ ['one_page' => '1 page', 'two_pages' => '2 pages', 'full' => 'Complet'][$template->recommended_pages] ?? '2 pages' }}</span>
                    </div>
                    @if($template->ats_friendly)
                        <p class="ats-note text-muted">Rubriques standards, lecture linéaire et texte sélectionnable pour les logiciels de recrutement.</p>
                    @endif
                    <div class="d-flex flex-wrap gap-2">
                        <x-admin.icon-action :href="route('admin.resume-templates.preview', $template)" size="sm" variant="outline-secondary" icon="bi-eye" :label="'Prévisualiser '.$template->name" />
                        <x-admin.icon-action :href="route('admin.resumes.index', ['template' => $template->id])" size="sm" variant="primary" icon="bi-check-lg" :label="'Utiliser '.$template->name" />
                        <x-admin.icon-action :href="route('admin.resume-templates.edit', $template)" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier '.$template->name" />
                        <form method="POST" action="{{ route('admin.resume-templates.duplicate', $template) }}">
                            @csrf
                            <x-admin.icon-action type="submit" size="sm" variant="outline-secondary" icon="bi-copy" :label="'Dupliquer '.$template->name" />
                        </form>
                        <form method="POST" action="{{ route('admin.resume-templates.toggle', $template) }}">
                            @csrf @method('PATCH')
                            <x-admin.icon-action type="submit" size="sm" :variant="$template->is_active ? 'outline-warning' : 'outline-success'" :icon="$template->is_active ? 'bi-toggle-on' : 'bi-toggle-off'" :label="$template->is_active ? 'Désactiver '.$template->name : 'Activer '.$template->name" />
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="text-muted">Aucun modèle correspondant à ce filtre.</p>
        @endforelse
    </div>
    <x-admin.pagination :paginator="$templates" />
</div>
@endsection

@push('scripts')
<script>
document.getElementById('showInactiveTemplates')?.addEventListener('change', (event) => {
    const url = new URL(window.location.href);
    event.target.checked ? url.searchParams.set('inactive', '1') : url.searchParams.delete('inactive');
    window.location.href = url.toString();
});
</script>
@endpush
