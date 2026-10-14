@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Modifier le CV</h2>
        <x-admin.icon-action :href="route('admin.resumes.index')" icon="bi-arrow-left" label="Retour à la liste des CV" variant="outline-secondary" />
    </div>

    <div class="card card-body">
        @include('admin.resumes._edit-form')
    </div>
</div>
@endsection
