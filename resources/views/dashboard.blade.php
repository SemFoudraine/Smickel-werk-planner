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
    </div>
@endsection
