@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Ruil Verzoek</div>
                    <div class="card-body">
                        <form method="POST" id="swapRequestForm"
                            action="{{ route('roosters.processSwapRequest', ['id' => $roosterId]) }}">
                            @csrf
                            <div class="form-group">
                                <label for="selectedDate">Datum van het geselecteerde rooster:</label>
                                <input type="text" class="form-control" name="selectedDate" id="selectedDate"
                                    value="{{ $roosterDate }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="selectedUser">Werknemer:</label>
                                <select class="form-control" name="selectedUser" id="selectedUser">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $user->id == $selectedUserId ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="userDates">Datum kiezen:</label>
                                <select class="form-control" name="userDates[]" id="userDates">
                                    {{-- Voeg de datums en tijden van de geselecteerde gebruiker toe --}}
                                    @foreach ($selectedUserDates as $date)
                                        @php
                                            // $date is a Y-m-d formatted string, so let's find the corresponding roster entry
$rosterEntry = $roosters->where('datum', $date)->first();
$startTime = optional($rosterEntry)->start_tijd ? optional($rosterEntry)->start_tijd->format('H:i') : 'Geen starttijd';
$endTime = optional($rosterEntry)->eind_tijd ? optional($rosterEntry)->eind_tijd->format('H:i') : 'Geen eindtijd';
                                        @endphp
                                        <option value="{{ $date }}">
                                            {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }} | {{ $startTime }} -
                                            {{ $endTime }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Ruil Rooster</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>


    <script>
        $(document).ready(function() {
            // Initialiseer Select2 op het element met id "userDates"
            $('#userDates').select2({
                placeholder: 'Zoek datum',
                language: 'nl', // Nederlands als taal
            });

            // Voeg een luisteraar toe voor het wijzigen van de geselecteerde gebruiker
            $('#selectedUser').on('change', function() {
                // Haal de geselecteerde gebruiker-ID op
                var selectedUserId = $(this).val();
                console.log('Selected User ID:', selectedUserId);

                // Voer een AJAX-aanroep uit om ingeroosterde datums en tijden op te halen voor de geselecteerde gebruiker
                $.ajax({
                    url: '/get-user-dates/' + selectedUserId,
                    type: 'GET',
                    success: function(response) {
                        console.log('User Dates:', response.userDates);

                        // Leeg de huidige opties in de "userDates" select box
                        $('#userDates').empty();

                        // Sorteer de datums in oplopende volgorde
                        response.userDates.sort(function(a, b) {
                            return new Date(a.datum) - new Date(b.datum);
                        });

                        // Voeg de nieuwe opties toe aan de "userDates" select box
                        if (response.userDates.length > 0) {
                            response.userDates.forEach(function(date) {
                                // Gebruik de date.tijd property om de tijd weer te geven
                                var formattedDate = moment(date.datum).format(
                                    'DD-MM-YYYY');
                                $('#userDates').append('<option value="' + date.datum +
                                    '">' +
                                    formattedDate + ' | ' + date.tijd + '</option>');
                            });
                        } else {
                            console.log('No userDates found for the selected user.');
                        }

                        // Vernieuw Select2 na het bijwerken van de opties
                        $('#userDates').trigger('change');

                        // Bijgewerkte regel om de geselecteerde gebruiker-ID door te geven
                        $('#swapRequestForm').attr('action',
                            '{{ route('roosters.processSwapRequest', ['id' => $roosterId]) }}' +
                            '/' + selectedUserId);
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.error("Error: " + textStatus, errorThrown);
                        console.log(xhr.responseText);
                    }
                });
            });

            // Roep de 'change' gebeurtenis handmatig op om de datums voor de standaard geselecteerde gebruiker te laden
            $('#selectedUser').trigger('change');
        });
    </script>
@endsection
