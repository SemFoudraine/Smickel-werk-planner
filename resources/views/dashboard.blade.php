@extends('layouts.app')

@section('content')
    @if (Auth::user()->hasRole('admin'))
        @if (\App\Models\Verlofaanvraag::where('status', 'In Afwachting')->exists())
            <div class="alert alert-warning">
                Er zijn nog verlofaanvragen die in behandeling genomen moeten worden. <a href="verlofaanvragen">Klik hier</a>
            </div>
        @endif
    @endif
    <div class="container mx-auto">
        <h1 class="text-white text-3xl font-bold mb-4">Dashboard</h1>

        <div class="bg-gray-200 p-4 rounded-lg mb-4">
            @if (isset($roostersThisWeek) && !$roostersThisWeek->isEmpty())
                <h2 class="text-lg font-semibold mb-2">Persoonlijk Rooster:</h2>
                <div class="grid grid-cols-2 gap-4">
                    @foreach ($roostersThisWeek as $rooster)
                        <div class="bg-white p-3 rounded-lg shadow-md">
                            <p class="font-semibold mb-1" style="margin-top: 0;">
                                {{ ucfirst(\Carbon\Carbon::parse($rooster->datum)->locale('nl')->isoFormat('dddd, D MMMM YYYY')) }}
                            </p>
                            <p style="margin-top: -8px;">
                                {{ \Carbon\Carbon::parse($rooster->start_tijd)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($rooster->eind_tijd)->format('H:i') }}
                                @if ($rooster->taak)
                                    | <span style="font-weight: bold;">{{ $rooster->taak->naam }}</span>
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">Er zijn geen roosters voor deze week.</p>
            @endif
        </div>

        <div class="bg-gray-200 p-4 rounded-lg">
            <h2 class="text-lg font-semibold mb-2">Totaal aantal uren deze maand:</h2>
            <p class="text-gray-600">{{ $totalHoursThisMonth }} uren</p>
        </div>

        <br>

        <div class="bg-gray-200 p-4 rounded-lg mb-4">
            <h2 class="text-lg font-semibold mb-2">Totaal aantal uren deze week:</h2>
            <p class="text-gray-600">{{ $totalHoursThisWeek }} uren</p>
        </div>

        <br>

        @if (Auth::user()->hasRole('admin'))
            <div class="mt-8">
                <h2 class="text-white text-2xl font-bold mb-4">Admin Dashboard</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- Totaal aantal uren deze maand --}}
                    <div class="bg-blue-200 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Totaal aantal uren deze maand:</h3>
                        <p class="text-gray-600">{{ $adminDashboard['totalHoursThisMonth'] }} uren</p>
                    </div>

                    {{-- Totaal aantal uren deze week --}}
                    <div class="bg-green-200 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Totaal aantal uren deze week:</h3>
                        <p class="text-gray-600">{{ $adminDashboard['totalHoursThisWeek'] }} uren</p>
                    </div>

                    {{-- Aantal werknemers --}}
                    <div class="bg-red-200 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Aantal werknemers:</h3>
                        <p class="text-gray-600">{{ $adminDashboard['totalEmployees'] }} werknemers</p>
                    </div>

                    {{-- Recent geregistreerde gebruikers --}}
                    <div class="bg-yellow-200 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Recent geregistreerde werknemers:</h3>
                        <ul>
                            @foreach ($adminDashboard['recentlyRegisteredUsers'] as $user)
                                <li>{{ $user->name }} -
                                    {{ $user->created_at ? $user->created_at->format('d-m-Y') : 'Datum onbekend' }}</li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Aantal verlofaanvragen per status --}}
                    <div class="bg-purple-200 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Verlofaanvragen per status:</h3>
                        <ul>
                            @foreach ($adminDashboard['leaveRequestCounts'] as $request)
                                <li>{{ ucfirst($request->status) }}: {{ $request->total }}</li>
                            @endforeach
                            <a href="verlofaanvragen" class="btn btn-primary mt-3" data-admin-action>Verlof Aanvragen</a>
                        </ul>
                    </div>

                    {{-- Meest actieve gebruikers op basis van het aantal uren deze maand --}}
                    <div class="bg-pink-200 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Meest gewerkte uren deze maand:</h3>
                        <ul>
                            @foreach ($adminDashboard['mostActiveUsers'] as $user)
                                <li>{{ $user->user->name }} - {{ $user->total_hours }} uren</li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Gemiddelde aantal uren gewerkt --}}
                    <div class="bg-orange-200 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Gemiddelde aantal uren gewerkt:</h3>
                        <p class="text-gray-600">{{ number_format($adminDashboard['averageHoursWorked'], 2) }} uren</p>
                    </div>

                </div>
            </div>
        @endif
    </div>
@endsection
