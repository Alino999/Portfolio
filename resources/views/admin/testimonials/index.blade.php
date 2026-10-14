@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Témoignages</h2>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">Ajouter un témoignage</div>
        <div class="card-body">
            @include('admin.testimonials._form', ['testimonial' => null, 'action' => route('admin.testimonials.store'), 'method' => 'POST'])
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Témoignages enregistrés</div>
        <div class="card-body pb-0">
            <x-admin.bulk-actions type="testimonials" :actions="['publish' => 'Autoriser / publier', 'unpublish' => 'Masquer', 'delete' => 'Supprimer']" />
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th></th><th>Auteur</th><th>Entreprise</th><th>Note</th><th>Publication</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($testimonials as $testimonial)
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $testimonial->id }}" form="bulk-action-testimonials" data-bulk-item aria-label="Sélectionner le témoignage de {{ $testimonial->author }}"></td>
                            <td>{{ $testimonial->author }}</td>
                            <td>{{ $testimonial->company ?? '—' }}</td>
                            <td>{{ $testimonial->rating }} / 5</td>
                            <td><span class="badge {{ $testimonial->published ? 'bg-success' : ($testimonial->submitted_by_client ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $testimonial->published ? 'Publié' : ($testimonial->submitted_by_client ? 'À autoriser' : 'Masqué') }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="{{ route('admin.testimonials.toggle-publication', $testimonial) }}">
                                        @csrf @method('PATCH')
                                        <x-admin.icon-action type="submit" size="sm" variant="{{ $testimonial->published ? 'outline-secondary' : 'outline-success' }}" icon="{{ $testimonial->published ? 'bi-eye-slash' : 'bi-check-lg' }}" :label="$testimonial->published ? 'Masquer le témoignage' : 'Autoriser et publier le témoignage'" />
                                    </form>
                                    <x-admin.icon-action :href="route('admin.testimonials.edit', $testimonial)" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier le témoignage de '.$testimonial->author" />
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Supprimer ce témoignage ?')">
                                        @csrf @method('DELETE')
                                        <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" label="Supprimer ce témoignage" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun témoignage enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$testimonials" />
    </div>
</div>
@endsection
