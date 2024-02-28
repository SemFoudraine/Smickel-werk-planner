@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-white">Gebruikersprofiel</h1>
        <div class="bg-dark p-4 rounded">
            <p class="text-white"><strong>Naam:</strong> {{ $user->name }}</p>
            <p class="text-white"><strong>Email:</strong> {{ $user->email }}</p>
            <p class="text-white"><strong>Telefoon:</strong> {{ $user->phone ?? 'N/A' }}</p>
            <p class="text-white"><strong>Geboorte Datum:</strong> {{ $user->birthdate ?? 'N/A' }}</p>
            <!-- Voeg meer velden toe indien nodig -->
        </div>
    </div>
@endsection
