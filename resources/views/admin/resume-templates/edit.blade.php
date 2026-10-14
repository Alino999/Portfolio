@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">Modifier le modèle</h1>
        <x-admin.icon-action :href="route('admin.resume-templates.index')" icon="bi-arrow-left" label="Retour à la bibliothèque" variant="outline-secondary" />
    </div>
    <div class="card card-body">
        @include('admin.resume-templates._form', [
            'template' => $template,
            'action' => route('admin.resume-templates.update', $template),
            'method' => 'PUT',
        ])
    </div>
</div>
@endsection
