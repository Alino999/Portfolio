@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Modifier le témoignage</h2>
        <x-admin.icon-action :href="route('admin.testimonials.index')" icon="bi-arrow-left" label="Retour aux témoignages" variant="outline-secondary" />
    </div>
    <div class="card shadow-sm"><div class="card-body">
        @include('admin.testimonials._form', ['testimonial' => $testimonial, 'action' => route('admin.testimonials.update', $testimonial), 'method' => 'PUT'])
    </div></div>
</div>
@endsection
