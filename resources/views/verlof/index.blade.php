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

    <div class="container" style="color: white;">
        @if (Auth::user()->hasRole('admin'))
            @if (\App\Models\Verlofaanvraag::where('status', 'In Afwachting')->exists())
                <div class="alert alert-warning">
                    Er zijn nog verlofaanvragen die in behandeling genomen moeten worden.
                </div>
            @endif
        @endif
        <h1>Verlofaanvragen</h1>

        <div class="verlof-form" style="color: white;">
            <form action="{{ route('verlofaanvragen.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="datum" style="color: white;">Begin Datum:</label>
                    <input type="date" id="datum" name="datum" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="eind_datum" style="color: white;">Eind Datum:</label>
                    <input type="date" id="eind_datum" name="eind_datum" class="form-control">
                    <small style="color: white;">Laat leeg als het om één dag gaat.</small>
                </div>
                <div class="form-group">
                    <label for="reden" style="color: white;">Reden:</label>
                    <textarea id="reden" name="reden" rows="4" class="form-control" required style="color: black;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Verlof aanvragen</button>
            </form>
        </div>

        <div class="verlofaanvragen mt-5">
            <h2>Overzicht verlofaanvragen</h2>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Gebruiker</th>
                        <th>Datum</th>
                        <th>Eind Datum</th>
                        <th>Reden</th>
                        <th>Status</th>
                        @if (auth()->user()->isAdmin())
                            <th>Actie</th>
                        @endif
                    </tr>
                </thead>
                <tbody style="color: white;">
                    @foreach ($verlofaanvragen as $verlofaanvraag)
                        <tr>
                            <td>{{ $verlofaanvraag->user->name ?? 'Gebruiker niet gevonden' }}</td>
                            <td>{{ \Carbon\Carbon::parse($verlofaanvraag->datum)->format('d-m-Y') }}</td>
                            <td>{{ $verlofaanvraag->eind_datum ? \Carbon\Carbon::parse($verlofaanvraag->eind_datum)->format('d-m-Y') : 'N.V.T' }}
                            </td>
                            <td>{{ $verlofaanvraag->reden }}</td>
                            <td
                                style="color: {{ $verlofaanvraag->status === 'In Afwachting' ? 'yellow' : ($verlofaanvraag->status === 'Afgekeurd' ? 'red' : 'green') }}">
                                {{ $verlofaanvraag->status }}
                            </td>
                            @if (auth()->user()->isAdmin())
                                <td class="text-center">
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
