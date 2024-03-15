<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

@extends('layouts.app')

@section('content')
    @if (Auth::user()->hasRole('admin'))
        @if (\App\Models\Verlofaanvraag::where('status', 'In Afwachting')->exists())
            <div class="alert alert-warning">
                Er zijn nog verlofaanvragen die in behandeling genomen moeten worden. <a href="verlofaanvragen">Klik hier</a>
            </div>
        @endif

        <div class="text-center mb-4">
            <button id="toggleAdminView" class="btn btn-info">Stop Bewerken</button>
        </div>
    @endif

    @if (Auth::user()->phone === null || Auth::user()->birthdate === null)
        <div class="alert alert-warning">
            Let op: Je hebt nog niet alle gegevens ingevuld. <x-nav-link :href="route('users.edit', auth()->user()->id)">Compleet maken</x-nav-link>
        </div>
    @endif

    <div class="container mt-4">
        <h1 class="text-center mb-4 text-black dark:text-white">Roosters | Week {{ $currentWeek }} | <button id="toggleMyRoosters"
                class="btn btn-secondary btn-sm">Persoonlijk Rooster</button></h1>
        <div class="text-center mb-4">
            <!-- Week Navigation -->
            <a href="{{ route('roosters.index', ['week' => $previousWeek]) }}" class="btn btn-primary btn-sm mr-2">
                Vorige Week
            </a>
            <a href="{{ route('roosters.index', ['week' => \Carbon\Carbon::now()->isoWeek()]) }}"
                class="btn btn-primary btn-sm mr-2">
                Huidige Week
            </a>
            <a href="{{ route('roosters.index', ['week' => $nextWeek]) }}" class="btn btn-primary btn-sm mr-2">
                Volgende Week
            </a>
        </div>

        <!-- Rooster Container -->
        <div class="rooster-container">
            @foreach ($days as $day)
                <div class="day-container">
                    <div class="day-header">
                        {{ ucfirst($day->locale('nl')->isoFormat('dddd')) }}<br>{{ $day->format('d M Y') }}
                        @if (Auth::user()->hasRole('admin'))
                            <a href="{{ route('addRooster', ['date' => $day->toDateString()]) }}" data-admin-action>
                                <button class="btn btn-add mt-2">Toevoegen</button>
                            </a>
                        @endif
                    </div>
                    <div class="day-events">
                        @php
                            $roostersOnDay = $roosters->filter(function ($rooster) use ($day) {
                                return $rooster->datum === $day->toDateString();
                            });
                        @endphp

                        @foreach ($roostersOnDay as $rooster)
                            <div class="event" data-user-id="{{ $rooster->user_id }}">
                                <strong>
                                    @if ($rooster->user)
                                        <!-- Altijd de naam weergeven -->
                                        {{ $rooster->user->name }}
                                    @else
                                        Geen gebruiker gevonden
                                    @endif
                                </strong>
                                <br>
                                <span>{{ \Carbon\Carbon::parse($rooster->start_tijd)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($rooster->eind_tijd)->format('H:i') }}</span>
                                @if ($rooster->taak)
                                    <br><strong>{{ $rooster->taak->naam }}</strong><br>
                                @endif

                                <!-- Conditioneel renderen van de link voor admins met behulp van een inline if-statement -->
                                @if (Auth::user()->hasRole('admin'))
                                    <div class="btn-group flex-column flex-md-row" role="group"
                                        aria-label="Rooster Actions" data-admin-action>
                                        <form method="POST"
                                            action="{{ route('removeFromRooster', ['roosterId' => $rooster->id]) }}"
                                            data-admin-action>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class='bx bxs-trash'></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('roosters.edit', ['id' => $rooster->id]) }}"
                                            class="btn btn-primary btn-sm" data-admin-action>
                                            <i class='bx bxs-edit'></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if ($roostersOnDay->isEmpty())
                            <div class="event no-data">Geen data gevonden</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        // Functie om de weergave van roosters te toggelen
        function toggleRoosters(showOnlyMyRoosters) {
            var myUserId = '{{ Auth::id() }}';
            var events = document.querySelectorAll('.event[data-user-id]');
            var noDataBlocks = document.querySelectorAll('.no-data');

            events.forEach(function(event) {
                if (showOnlyMyRoosters) {
                    if (event.dataset.userId !== myUserId) {
                        event.classList.add('d-none');
                    } else {
                        event.classList.remove('d-none');
                    }
                } else {
                    event.classList.remove('d-none');
                }
            });

            noDataBlocks.forEach(function(block) {
                if (showOnlyMyRoosters) {
                    block.classList.add('d-none');
                } else {
                    block.classList.remove('d-none');
                }
            });

            document.getElementById('toggleMyRoosters').textContent = showOnlyMyRoosters ? 'Groeps Rooster' :
                'Persoonlijk Rooster';
            localStorage.setItem('showOnlyMyRoosters', showOnlyMyRoosters);
        }

        // Controleer localStorage om de status te bepalen wanneer de pagina laadt
        document.addEventListener('DOMContentLoaded', function() {
            var showOnlyMyRoosters = localStorage.getItem('showOnlyMyRoosters') === 'true';
            toggleRoosters(showOnlyMyRoosters);

            // Controleer of 'isAdminView' in local storage is opgeslagen
            var isAdminViewStored = localStorage.getItem('isAdminView');
            var isAdminView = isAdminViewStored ? JSON.parse(isAdminViewStored) : true; // Standaardwaarde is true

            // Update de admin-acties en de knoptekst op basis van 'isAdminView'
            updateAdminView(isAdminView);
        });

        // Functie om de adminweergave te updaten
        function updateAdminView(isAdminView) {
            var adminActions = document.querySelectorAll('[data-admin-action]');
            adminActions.forEach(function(element) {
                element.style.display = isAdminView ? '' : 'none';
            });

            document.getElementById('toggleAdminView').textContent = isAdminView ? 'Stop Bewerken' : 'Bewerk Rooster';
        }

        // Event listener voor de rooster toggle knop
        document.getElementById('toggleMyRoosters').addEventListener('click', function() {
            var showOnlyMyRoosters = localStorage.getItem('showOnlyMyRoosters') !== 'true';
            toggleRoosters(showOnlyMyRoosters);
        });

        // Event listener voor de adminweergave toggle knop
        document.getElementById('toggleAdminView').addEventListener('click', function() {
            var isAdminView = this.textContent.includes('Stop Bewerken');
            isAdminView = !isAdminView; // Toggle de status
            localStorage.setItem('isAdminView', JSON.stringify(
            isAdminView)); // Sla de nieuwe status op in local storage
            updateAdminView(isAdminView); // Update de weergave
        });
    </script>

@endsection

<style>
    body {
        background-color: #f8f9fa;
        font-family: Arial, sans-serif;
    }

    h1 {
        color: #333;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }

    .day-container .btn-add {
        width: 100%;
        background-color: #28a745;
        /* Groene achtergrond */
        color: white;
        /* Witte tekst */
        border: none;
        padding: 4px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: center;
        font-weight: bold;
    }

    .d-none {
        display: none !important;
    }

    .day-container .btn-add:hover {
        background-color: #2fd252;
        color: white;
        /* Donkergroene tint voor hover effect */
    }


    .event {
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
        margin-top: 10px;
        text-align: center;
        font-size: 14px;
        line-height: 1.4;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-group .btn {
        padding: 0.35rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0;
        max-height: 1.8rem;
        margin-right: 6px;
    }

    .btn-group .btn i {
        font-size: 1rem;
    }

    .btn-group .btn:first-child {
        border-radius: 2px 2px 2px 2px;
    }

    .btn-group .btn:last-child {
        border-radius: 2px 2px 2px 2px;
    }

    /* Responsive layout styles */
    @media (max-width: 767.98px) {
        .btn-group {
            display: flex;
            flex-direction: column;
            /* Knoppen onder elkaar */
            width: 100%;
        }

        .btn-group .btn {
            width: 100%;
            /* Volledige breedte voor elke knop */
            margin-top: 4px;
            /* Ruimte tussen de knoppen */
        }
    }

    @media (min-width: 768px) {
        .event {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .btn-group {
            flex-direction: row;
        }
    }

    .day-header {
        background-color: #004692;
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        font-weight: bold;
    }

    .day-header a {
        color: #fff;
        text-decoration: none;
    }

    .day-header a:hover {
        text-decoration: underline;
    }

    /* Aanvullende responsieve lay-outstijlen */
    @media (min-width: 1200px) {
        .rooster-container {
            display: flex;
            justify-content: space-between;
        }

        .day-container {
            flex: 1;
            margin-right: 10px;
        }

        .day-container:last-child {
            margin-right: 0;
        }
    }

    @media (max-width: 995px) {
        .rooster-container {
            display: block;
        }

        .day-container {
            margin-bottom: 20px;
        }

        .day-header,
        .day-events {
            margin-left: 10px;
            margin-right: 10px;
        }
    }

    /* Stijlen voor de dagen van de week */
    .day-header {
        background-color: #004692;
        color: #fff;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        font-weight: bold;
    }

    .day-header a {
        color: #fff;
        text-decoration: none;
    }

    .day-header a:hover {
        text-decoration: underline;
    }
</style>
