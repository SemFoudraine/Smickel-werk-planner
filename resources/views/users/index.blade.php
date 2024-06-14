@extends('layouts.app')

@section('content')
    <div class="container">
        @if (\App\Models\Verlofaanvraag::where('status', 'In Afwachting')->exists())
            <div class="alert alert-warning">
                Er zijn nog verlofaanvragen die in behandeling genomen moeten worden. <a href="verlofaanvragen">Klik hier</a>
            </div>
        @endif

        <a href="{{ url('werkplanner.apk') }}">Download de app</a>

        <h1 class="text-black dark:text-white mb-4">Werknemers <a href="/users/create" class="btn btn-primary">+</a></h1>

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <ul class="list-group">
            @foreach ($users as $user)
                <li class="list-group-item bg-transparent border-0 mb-2">
                    <div class="p-3 rounded bg-blue-900 text-white dark:text-white dark:bg-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $user->name }}</span>
                            <div class="btn-group" role="group" aria-label="Gebruikersacties">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                    <i class='bx bxs-edit-alt'></i>
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">
                                        <i class='bx bxs-trash'></i>
                                    </button>
                                </form>
                                <a href="{{ route('users.roles', $user->id) }}" class="btn btn-sm btn-success">
                                    <i class='bx bxs-user-detail'></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endsection

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>
    .list-group-item {
        background-color: transparent !important;
        border: none !important;
    }

    /* Positie en uitlijning van knoppen */
    .btn-group {
        margin-left: auto;
    }

    /* Knopstijlen */
    .btn {
        margin-right: 5px;
        width: 35px;
        /* Breedte van de knoppen */
        height: 35px;
        /* Hoogte van de knoppen */
    }
</style>
