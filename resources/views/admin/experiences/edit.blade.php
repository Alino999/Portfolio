@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Modifier l’expérience</h2>
        <x-admin.icon-action :href="route('admin.experiences.index')" icon="bi-arrow-left" label="Retour aux expériences" variant="outline-secondary" />
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @include('admin.experiences._form', [
                'experience' => $experience,
                'action' => route('admin.experiences.update', $experience),
                'method' => 'PUT',
            ])
        </div>
    </div>
</div>
@endsection
