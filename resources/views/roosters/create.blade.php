@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1 class="text-center mb-4" style="color: white;">Voeg rooster toe voor {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h1>
        <div class="row">
            <div class="col-md-6">
                <form id="roosterForm" action="{{ route('storeRooster', ['date' => $date]) }}" method="POST">
                    @csrf

                    <!-- Add form fields for rooster creation -->
                    <div class="form-group">
                        <label for="start_tijd" style="color: white;">Start Tijd:</label>
                        <input type="time" class="form-control" id="start_tijd" name="start_tijd">
                    </div>

                    <div class="form-group">
                        <label for="eind_tijd" style="color: white;">Eind Tijd:</label>
                        <input type="time" class="form-control" id="eind_tijd" name="eind_tijd">
                    </div>

                    <div class="form-group">
                        <label for="datum" style="color: white;">Datum:</label>
                        <input type="date" class="form-control" id="datum" name="datum" value="{{ $date }}"
                            readonly>
                    </div>

                    <!-- Dropdown to select a user -->
                    <div class="form-group">
                        <label for="user_id" style="color: white;">Persoon:</label>
                        <select class="form-control" id="user_id" name="user_id">
                            @foreach ($users as $user)
                                @php
                                    $verlofStatus = $user
                                        ->verlofaanvragen()
                                        ->where('datum', $date)
                                        ->value('status');
                                @endphp
                                <option value="{{ $user->id }}" data-verlof="{{ $verlofStatus }}">{{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dropdown to select a task -->
                    <div class="form-group">
                        <label for="taak_id" style="color: white;">Taak:</label>
                        <select class="form-control" id="taak_id" name="taak_id">
                            <option value="" selected>Kies een taak (optioneel)</option>
                            @foreach ($taken as $taak)
                                <option value="{{ $taak->id }}">{{ $taak->naam }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" id="createRoosterBtn">Voeg toe</button>
                </form>
            </div>

            <div class="col-md-6">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <h3 class="card-title">Verlofaanvragen voor {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</h3>
                        @if ($verlofaanvragen->isEmpty())
                            <p>Geen verlof aanvragen voor deze datum.</p>
                        @else
                            <ul style="list-style: none; padding: 0;">
                                @foreach ($verlofaanvragen as $verlofaanvraag)
                                    @php
                                        $statusColor = '';
                                        switch ($verlofaanvraag->status) {
                                            case 'Goedgekeurd':
                                                $statusColor = 'green';
                                                break;
                                            case 'In Afwachting':
                                                $statusColor = 'yellow';
                                                break;
                                            case 'Afgekeurd':
                                                $statusColor = 'red';
                                                break;
                                            default:
                                                $statusColor = 'white'; // Default color
                                        }
                                    @endphp
                                    <li style="cursor: pointer;">
                                        {{ $verlofaanvraag->user->name }} - Status:
                                        <span style="color: {{ $statusColor }}">{{ $verlofaanvraag->status }}</span>
                                        <div class="collapse mt-2">
                                            Reden: {{ $verlofaanvraag->reden }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('roosterForm').addEventListener('submit', function(event) {
                const userSelect = document.getElementById('user_id');
                const selectedOption = userSelect.options[userSelect.selectedIndex];
                const verlofStatus = selectedOption.getAttribute('data-verlof');

                if (verlofStatus === 'Goedgekeurd') {
                    const confirmation = confirm(
                        'Deze persoon heeft goedgekeurd verlof. Weet je zeker dat je deze persoon wilt inplannen?'
                    );
                    if (!confirmation) {
                        event.preventDefault(); // Prevent form submission if user cancels
                    }
                }
            });
        });
    </script>
@endsection
