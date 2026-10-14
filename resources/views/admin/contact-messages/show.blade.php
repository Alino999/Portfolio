@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2>{{ $contactMessage->subject }}</h2>
    <p><strong>Nom:</strong> {{ $contactMessage->name }}</p>
    <p><strong>Email:</strong> {{ $contactMessage->email }}</p>
    <p><strong>Téléphone:</strong> {{ $contactMessage->phone ?? 'N/A' }}</p>
    <div class="card card-body mt-3">{{ $contactMessage->message }}</div>
</div>
@endsection
