@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-black dark:text-white">Rollen beheren voor {{ $user->name }}</h1>

        <div class="mt-4">
            <h2 class="text-black dark:text-white">Huidige rollen:</h2>
            <ul class="list-group">
                @foreach ($roles as $role)
                    <li class="list-group-item bg-dark text-white">{{ $role->name }}</li>
                @endforeach
            </ul>
        </div>

        @if (!$user->hasRole('admin'))
            <div class="mt-4">
                <form action="{{ route('users.assignRole', $user->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="admin">
                    <button type="submit" class="btn btn-success">Voeg Admin Rol Toe</button>
                </form>
            </div>
        @endif

        @if ($user->hasRole('admin') && auth()->user()->id !== $user->id)
            <div class="mt-2">
                <form action="{{ route('users.removeRole', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="role" value="admin">
                    <button type="submit" class="btn btn-danger mt-2">Verwijder Admin Rol</button>
                </form>
            </div>
        @endif
    </div>
@endsection
