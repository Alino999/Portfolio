@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Modifier : {{ $definition['singular'] }}</h1>
        <x-admin.icon-action :href="route('admin.portfolio-content.index', $type)" icon="bi-arrow-left" label="Retour aux contenus" variant="outline-secondary" />
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="card shadow-sm"><div class="card-body">
        @include('admin.portfolio-content._form', ['item' => $item])
    </div></div>
</div>
@endsection