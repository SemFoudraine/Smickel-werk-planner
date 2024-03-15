@extends('layouts.app')
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

@section('content')
    <style>
        .verlofaanvragen tr {
            height: 50px;
        }

        .verlofaanvragen .btn {
            margin-right: 5px;
        }
    </style>

    <div class="container text-black dark:text-white mx-auto p-4">
        @if (Auth::user()->hasRole('admin'))
            @if (\App\Models\Verlofaanvraag::where('status', 'In Afwachting')->exists())
                <div class="alert alert-warning">
                    Er zijn nog verlofaanvragen die in behandeling genomen moeten worden.
                </div>
            @endif
        @endif
        <h1>Verlofaanvragen</h1>

        <div class="verlof-form text-black dark:text-white">
            <form action="{{ route('verlofaanvragen.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="form-group">
                    <label for="datum" class="text-black dark:text-white block">Begin Datum:</label>
                    <input type="date" id="datum" name="datum" class="form-control text-black dark:text-white"
                        required>
                </div>
                <div class="form-group">
                    <label for="eind_datum" class="text-black dark:text-white block">Eind Datum:</label>
                    <input type="date" id="eind_datum" name="eind_datum" class="form-control text-black dark:text-white">
                    <small class="text-black dark:text-white">Laat leeg als het om één dag gaat.</small>
                </div>
                <div class="form-group">
                    <label for="reden" class="text-black dark:text-white block">Reden:</label>
                    <textarea id="reden" name="reden" rows="4" class="form-control text-black dark:text-white" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Verlof aanvragen</button>
            </form>
        </div>

        <div class="overflow-x-auto mt-6">
            <table class="table-auto w-full">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2">Gebruiker</th>
                        <th class="px-4 py-2">Datum</th>
                        <th class="px-4 py-2">Eind Datum</th>
                        <th class="px-4 py-2">Reden</th>
                        <th class="px-4 py-2">Status</th>
                        @if (auth()->user()->isAdmin())
                            <th class="px-4 py-2">Actie</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="text-black dark:text-white">
                    @foreach ($verlofaanvragen as $verlofaanvraag)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $verlofaanvraag->user->name ?? 'Gebruiker niet gevonden' }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($verlofaanvraag->datum)->format('d-m-Y') }}</td>
                            <td class="px-4 py-2">
                                {{ $verlofaanvraag->eind_datum ? \Carbon\Carbon::parse($verlofaanvraag->eind_datum)->format('d-m-Y') : 'N.V.T' }}
                            </td>
                            <td class="px-4 py-2">{{ $verlofaanvraag->reden }}</td>
                            <td class="px-4 py-2"
                                style="color: {{ $verlofaanvraag->status === 'In Afwachting' ? 'yellow' : ($verlofaanvraag->status === 'Afgekeurd' ? 'red' : 'green') }}">
                                {{ $verlofaanvraag->status }}
                            </td>
                            @if (auth()->user()->isAdmin())
                                <td class="px-4 py-2 text-center">
                                    @if ($verlofaanvraag->status === 'In Afwachting')
                                        <form action="{{ route('verlofaanvragen.approve', $verlofaanvraag->id) }}"
                                            method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-icon btn-success">
                                                <i class='bx bx-check'></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('verlofaanvragen.reject', $verlofaanvraag->id) }}"
                                            method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-icon btn-danger">
                                                <i class='bx bx-x'></i>
                                            </button>
                                        </form>
                                    @elseif ($verlofaanvraag->status === 'Goedgekeurd' || $verlofaanvraag->status === 'Afgekeurd')
                                        @if ($verlofaanvraag->status === 'Goedgekeurd')
                                            <form action="{{ route('verlofaanvragen.reject', $verlofaanvraag->id) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-icon btn-danger">
                                                    <i class='bx bx-x'></i>
                                                </button>
                                            </form>
                                        @elseif ($verlofaanvraag->status === 'Afgekeurd')
                                            <form action="{{ route('verlofaanvragen.approve', $verlofaanvraag->id) }}"
                                                method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-icon btn-success">
                                                    <i class='bx bx-check'></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('verlofaanvragen.delete', $verlofaanvraag->id) }}"
                                            method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-danger">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
