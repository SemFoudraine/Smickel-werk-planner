@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="text-center mb-4">Bewerk Rooster</h1>
        <form action="{{ route('roosters.update', ['id' => $rooster->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Datumveld -->
            <div class="form-group">
                <label for="datum">Datum:</label>
                <input type="date" class="form-control" id="datum" name="datum" value="{{ $rooster->datum }}">
            </div>

            <!-- Bewerkbare velden -->
            <div class="form-group">
                <label for="start_tijd">Start Tijd:</label>
                <input type="time" class="form-control" id="start_tijd" name="start_tijd"
                    value="{{ substr($rooster->start_tijd, 0, 5) }}">
            </div>

            <div class="form-group">
                <label for="eind_tijd">Eind Tijd:</label>
                <input type="time" class="form-control" id="eind_tijd" name="eind_tijd"
                    value="{{ substr($rooster->eind_tijd, 0, 5) }}">
            </div>

            <!-- Selecteer werknemer -->
            <div class="form-group">
                <label for="user_id">Werknemer:</label>
                <select class="form-control" id="user_id" name="user_id">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $user->id === $rooster->user_id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="taak_id">Taak:</label>
                <select class="form-control" id="taak_id" name="taak_id">
                    <option value="" {{ $rooster->taak_id ? '' : 'selected' }}>Kies een taak (optioneel)</option>
                    @foreach ($taken as $taak)
                        <option value="{{ $taak->id }}" {{ $taak->id == $rooster->taak_id ? 'selected' : '' }}>
                            {{ $taak->naam }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
            <button type="submit" class="btn btn-primary">Bijwerken</button>
                <a href="{{ route('requestSwap', ['id' => $rooster->id]) }}" class="btn btn-primary"
                    data-admin-action>Ruilen</a>
            </div>
        </form>
    </div>
@endsection


<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<style>
    h1 {
        color: white;
    }

    label {
        color: white;
    }
</style>
