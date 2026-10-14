@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Modifier le service</h2>
        <x-admin.icon-action :href="route('admin.services.index')" icon="bi-arrow-left" label="Retour aux services" variant="outline-secondary" />
    </div>
    <div class="card shadow-sm"><div class="card-body">
        @include('admin.services._form', ['service' => $service, 'action' => route('admin.services.update', $service), 'method' => 'PUT'])
    </div></div>
</div>
@endsection
