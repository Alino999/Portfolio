@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Messages reçus</h2>
    <x-admin.bulk-actions type="contact-messages" :actions="['mark-read' => 'Marquer comme lu', 'delete' => 'Supprimer']" />
    <div class="list-group">
        @forelse($messages as $message)
            <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                <input type="checkbox" class="form-check-input flex-shrink-0" name="ids[]" value="{{ $message->id }}" form="bulk-action-contact-messages" data-bulk-item aria-label="Sélectionner le message de {{ $message->name }}">
                <a href="{{ route('admin.messages.show', $message) }}" class="text-reset text-decoration-none flex-grow-1">
                    <div class="d-flex justify-content-between"><strong>{{ $message->name }}</strong><small>{{ $message->status }}</small></div>
                    <div>{{ $message->subject }}</div>
                    <small class="text-muted">{{ $message->email }}</small>
                </a>
                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                    @csrf
                    @method('DELETE')
                    <x-admin.icon-action type="submit" variant="outline-danger" size="sm" icon="bi-trash" :label="'Supprimer le message de '.$message->name" />
                </form>
            </div>
        @empty
            <div class="list-group-item text-muted">Aucun message reçu.</div>
        @endforelse
    </div>
    <x-admin.pagination :paginator="$messages" />
</div>
@endsection
