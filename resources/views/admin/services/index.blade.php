@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Services</h2>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">Ajouter un service</div>
        <div class="card-body">
            @include('admin.services._form', ['service' => null, 'action' => route('admin.services.store'), 'method' => 'POST'])
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Services enregistrés</div>
        <div class="card-body pb-0">
            <x-admin.bulk-actions type="services" :actions="['publish' => 'Publier', 'unpublish' => 'Masquer', 'delete' => 'Supprimer']" />
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th></th><th>Service</th><th>Ordre</th><th>Publication</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $service->id }}" form="bulk-action-services" data-bulk-item aria-label="Sélectionner {{ App\Support\LocaleHelper::text($service->title) }}"></td>
                            <td>{{ App\Support\LocaleHelper::text($service->title) }}</td>
                            <td>{{ $service->sort_order }}</td>
                            <td><span class="badge {{ $service->published ? 'bg-success' : 'bg-secondary' }}">{{ $service->published ? 'Publié' : 'Masqué' }}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <x-admin.icon-action :href="route('admin.services.edit', $service)" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier '.$service->title['fr']" />
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?')">
                                        @csrf @method('DELETE')
                                        <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" label="Supprimer ce service" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucun service enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$services" />
    </div>
</div>
@endsection
