@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-white">Bewerk Gebruiker</h1>

        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name" class="text-white">Naam:</label>
                <input type="text" id="name" name="name" value="{{ $user->name }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email" class="text-white">E-mailadres:</label>
                <input type="email" id="email" name="email" value="{{ $user->email }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="phone" class="text-white">Telefoon Nummer:</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="form-control" pattern="^\+[0-9]{2} [0-9]{1,2} [0-9]{8}$" title="Voer het telefoonnummer in het formaat +31 6 12345678 in" placeholder="+31 6 12345678" required>
                @error('phone')
                    <div class="alert alert-warning">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="birthdate" class="text-white">Geboorte Datum:</label>
                <input type="date" id="birthdate" name="birthdate" value="{{ $user->birthdate }}" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Opslaan</button>
        </form>
    </div>
@endsection
