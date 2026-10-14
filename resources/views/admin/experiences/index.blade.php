@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0">Expériences professionnelles</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">Ajouter une expérience</div>
        <div class="card-body">
            @include('admin.experiences._form', [
                'experience' => null,
                'action' => route('admin.experiences.store'),
                'method' => 'POST',
            ])
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Expériences enregistrées</div>
        <div class="card-body pb-0">
            <x-admin.bulk-actions type="experiences" :actions="['publish' => 'Publier', 'unpublish' => 'Masquer', 'delete' => 'Supprimer']" />
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Poste</th>
                        <th>Entreprise</th>
                        <th>Période</th>
                        <th>Publication</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($experiences as $experience)
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $experience->id }}" form="bulk-action-experiences" data-bulk-item aria-label="Sélectionner {{ $experience->position }} chez {{ $experience->company }}"></td>
                            <td>{{ $experience->position }}</td>
                            <td>{{ $experience->company }}</td>
                            <td>
                                {{ $experience->start_date?->format('d/m/Y') ?? '—' }}
                                –
                                {{ $experience->is_current ? 'Aujourd’hui' : ($experience->end_date?->format('d/m/Y') ?? '—') }}
                            </td>
                            <td>
                                <span class="badge {{ $experience->published ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $experience->published ? 'Publiée' : 'Masquée' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <x-admin.icon-action :href="route('admin.experiences.edit', $experience)" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier '.$experience->position.' chez '.$experience->company" />
                                    <form method="POST" action="{{ route('admin.experiences.destroy', $experience) }}" onsubmit="return confirm('Supprimer cette expérience ?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" label="Supprimer cette expérience" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucune expérience enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            <x-admin.pagination :paginator="$experiences" />
    </div>
</div>
@endsection
