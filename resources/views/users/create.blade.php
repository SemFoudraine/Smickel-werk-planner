@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="text-center mb-4 text-white">Gebruiker Toevoegen</h1>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="form-group text-white">
                    <label for="name">Naam:</label>
                    <input type="text" class="form-control" id="name" name="name" required autofocus>
                </div>

                <div class="form-group text-white">
                    <label for="email">E-mailadres:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="form-group text-white">
                    <label for="password">Wachtwoord:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="form-group text-white">
                    <label for="password_confirmation">Bevestig Wachtwoord:</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-primary">Gebruiker Toevoegen</button>
            </form>
        </div>
    </div>
</div>
@endsection
