<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Verlofaanvraag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Rooster;
use App\Models\SwapRequest;
use App\Models\User;
use App\Models\Taak;
use App\Models\Notification;

class RoosterController extends Controller
{
    public function index(Request $request)
    {
        // Bereken de huidige week op basis van de huidige datum
        $currentDate = Carbon::now();
        $currentWeek = $currentDate->isoWeek();

        // Haal het weeknummer uit de URL of gebruik het huidige weeknummer als standaard
        $currentWeek = $request->input('week') ?? $currentWeek;

        // Bereken de start- en einddatum van de huidige week
        $startDate = $currentDate->copy()->isoWeek($currentWeek)->startOfWeek();
        $endDate = $currentDate->copy()->isoWeek($currentWeek)->endOfWeek();

        // Bereken de weeknummers voor vorige en volgende week
        $previousWeek = $currentWeek - 1;
        $nextWeek = $currentWeek + 1;

        $days = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $days[] = $date->copy(); // Bewaar de Carbon datumobjecten in plaats van ze om te zetten naar strings
        }

        // Haal de roostergegevens op met bijbehorende gebruikers via de 'user' relatie
        $roosters = Rooster::with('user')
            ->whereBetween('start_tijd', [$startDate, $endDate])
            ->get();

        // Haal alle gebruikers op om beschikbaar te stellen in de weergave
        $users = User::all();

        return view('roosters.index', compact('days', 'roosters', 'users', 'startDate', 'endDate', 'currentWeek', 'previousWeek', 'nextWeek'));
        // return view('roosters.index', [
        //     'days' => $days,
        //     'roosters' => $roosters,
        //     'users' => User::all(),
        //     'startDate' => $startDate,
        //     'endDate' => $endDate,
        //     'currentWeek' => $currentWeek,
        //     'previousWeek' => $previousWeek,
        //     'nextWeek' => $nextWeek
        // ]);
    }


    private function createNotification($rooster)
    {
        // Controleer of de datum een instantie is van Carbon
        if (!$rooster->datum instanceof Carbon) {
            // Zo niet, maak er een Carbon instantie van
            $rooster->datum = new Carbon($rooster->datum);
        }

        $notification = new Notification();
        $notification->user_id = $rooster->user_id;
        $notification->title = 'Nieuwe Rooster Toewijzing';
        $notification->message = sprintf(
            "Je bent ingeroosterd op %s van %s tot %s",
            $rooster->datum->format('Y-m-d'),
            $rooster->start_tijd->format('H:i'),
            $rooster->eind_tijd->format('H:i')
        );

        $notification->save();
    }

    public function addRooster(Request $request)
    {
        $date = $request->input('date') ?? now()->format('Y-m-d');

        // Gebruikers en taken ophalen zoals voorheen
        $users = User::all();
        $taken = Taak::all();

        // Aangepaste logica om verlofaanvragen correct te verwerken
        $verlofaanvragen = Verlofaanvraag::where(function ($query) use ($date) {
            // Verlofaanvragen die op de opgegeven datum starten
            $query->where('datum', $date)
                // OF verlofaanvragen die voor of op de datum starten EN op of na de datum eindigen
                // Dit houdt rekening met verlofaanvragen die een bereik beslaan
                ->orWhere(function ($subQuery) use ($date) {
                    $subQuery->where('datum', '<=', $date)
                        ->where('eind_datum', '>=', $date);
                })
                // Specifieke check voor verlofaanvragen zonder einddatum
                // Hierdoor worden enkel verlofaanvragen zonder einddatum getoond als de datum overeenkomt
                ->orWhere(function ($subQuery) use ($date) {
                    $subQuery->where('datum', $date)
                        ->whereNull('eind_datum');
                });
        })->get();

        // Roosters voor de geselecteerde datum ophalen
        $roostersForDate = Rooster::where('datum', $date)->with('user')->get();

        return view('roosters.create', compact('date', 'users', 'verlofaanvragen', 'roostersForDate', 'taken'));
    }

    public function store(Request $request)
    {
        // Validation rules
        $validatedData = $request->validate([
            'user_id' => 'required',
            'datum' => 'required|date_format:Y-m-d',
            'start_tijd' => ['required', 'date_format:H:i'],
            'eind_tijd' => ['required', 'date_format:H:i'],
            'taak_id' => 'nullable|exists:taken,id',
        ]);

        try {
            // Converteer start_tijd en eind_tijd naar Carbon instances
            $startTijd = Carbon::createFromFormat('H:i', $request->start_tijd);
            $eindTijd = Carbon::createFromFormat('H:i', $request->eind_tijd);

            // Bereken de duur in uren
            $duurInUren = $eindTijd->diffInHours($startTijd);

            // Maak een nieuwe Rooster instance en sla deze op
            $rooster = new Rooster();
            $rooster->user_id = $validatedData['user_id'];
            $rooster->datum = $validatedData['datum'];
            $rooster->start_tijd = $startTijd->toTimeString(); // Sla de tijd op als een string
            $rooster->eind_tijd = $eindTijd->toTimeString(); // Sla de tijd op als een string
            $rooster->taak_id = $validatedData['taak_id'] ?? null; // Gebruik null als geen taak_id is gegeven
            $rooster->duur_in_uren = $duurInUren; // Wijs de berekende duur toe

            $rooster->save(); // Sla het rooster op in de database

            // Voeg een notificatie toe (indien nodig, afhankelijk van je applicatielogica)

            return redirect()->route('roosters.index')->with('success', 'Rooster succesvol toegevoegd.');
        } catch (\Exception $e) {
            // Log de error en toon een foutmelding aan de gebruiker
            Log::error($e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het toevoegen van het rooster.']);
        }
    }

    public function storeRooster(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required',
                'start_tijd' => ['required', 'regex:/^(0?[0-9]|1[0-9]|2[0-3])(:[0-5][0-9])?$/'],
                'eind_tijd' => ['required', 'regex:/^(0?[0-9]|1[0-9]|2[0-3])(:[0-5][0-9])?$/'],
                'datum' => 'required|date_format:Y-m-d',
                'taak_id' => 'nullable|exists:taken,id',
            ]);

            $startTime = $this->normalizeTime($validatedData['start_tijd']);
            $endTime = $this->normalizeTime($validatedData['eind_tijd']);

            $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $validatedData['datum'] . ' ' . $startTime);
            $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $validatedData['datum'] . ' ' . $endTime);

            // Bereken de duur in uren
            $duurInUren = $endDateTime->diffInHours($startDateTime);

            $rooster = new Rooster([
                'user_id' => $validatedData['user_id'],
                'start_tijd' => $startDateTime,
                'eind_tijd' => $endDateTime,
                'datum' => $validatedData['datum'],
                'taak_id' => $validatedData['taak_id'],
                'duur_in_uren' => $duurInUren, // Voeg deze regel toe om de berekende duur op te slaan
                'approval_status' => 'approved',
            ]);

            $rooster->save();

            $geformatteerdeDatum = $startDateTime->format('d-m-Y');
            $geformatteerdeStartTijd = $startDateTime->format('H:i');
            $geformatteerdeEindTijd = $endDateTime->format('H:i');

            // Create and save the notification
            $notification = new Notification([
                'user_id' => $rooster->user_id,
                'title' => 'Rooster wijziging',
                'message' => 'Je bent ingeroosterd op <strong>' . $geformatteerdeDatum .
                    '</strong> van <strong>' . $geformatteerdeStartTijd .
                    '</strong> tot <strong>' . $geformatteerdeEindTijd . '</strong>.',
                'is_read' => 0,
            ]);

            $notification->save();
            DB::commit();

            $week = $startDateTime->isoWeek();
            return redirect()->route('roosters.index', ['week' => $week])->with('success', 'Rooster is succesvol toegevoegd en notificatie verzonden.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het toevoegen van het rooster. Probeer het opnieuw.']);
        }
    }

    // Hulpmethode om de tijd te normaliseren naar HH:MM formaat
    private function normalizeTime($time)
    {
        // Voeg de ontbrekende :00 toe als de tijd geen minuten heeft
        if (strpos($time, ':') === false) {
            $time .= ':00';
        }

        return $time;
    }

    public function show(string $id)
    {
        // Logica voor het weergeven van een specifiek rooster (indien van toepassing)
    }

    public function edit($id)
    {
        $rooster = Rooster::findOrFail($id);
        $users = User::all();
        $date = $rooster->datum; // Haal de datum op uit het rooster

        // Voeg de lijst met taken toe aan de variabele $taken
        $taken = Taak::all(); // Zorg ervoor dat je de juiste modelnaam gebruikt en pas de query aan indien nodig

        return view('roosters.edit', compact('rooster', 'users', 'date', 'taken'));
    }



    public function update(Request $request, $id)
    {
        try {
            // Vind het rooster of geef een foutmelding als het niet gevonden wordt
            $rooster = Rooster::findOrFail($id);

            // Valideer de request data
            $validatedData = $request->validate([
                'user_id' => 'required',
                'datum' => 'required|date_format:Y-m-d',
                'start_tijd' => 'required|date_format:H:i',
                'eind_tijd' => 'required|date_format:H:i',
                'taak_id' => 'nullable|exists:taken,id',
            ]);

            // Zet de start- en eindtijd om naar Carbon instanties
            $startTijd = Carbon::createFromFormat('Y-m-d H:i', $validatedData['datum'] . ' ' . $validatedData['start_tijd']);
            $eindTijd = Carbon::createFromFormat('Y-m-d H:i', $validatedData['datum'] . ' ' . $validatedData['eind_tijd']);

            // Bereken de duur in uren
            $duurInUren = $eindTijd->diffInHours($startTijd);

            // Update het rooster inclusief de duur_in_uren
            $rooster->user_id = $validatedData['user_id'];
            $rooster->datum = $validatedData['datum'];
            $rooster->start_tijd = $startTijd->toTimeString();
            $rooster->eind_tijd = $eindTijd->toTimeString();
            $rooster->taak_id = $validatedData['taak_id'];
            $rooster->duur_in_uren = $duurInUren;

            $rooster->save();

            $userId = $rooster->user_id;
            $roosterDatum = Carbon::createFromFormat('Y-m-d', $rooster->datum)->format('d-m-Y');

            $notification = new Notification([
                'user_id' => $userId,
                'title' => 'Rooster Wijziging',
                'message' => "Jouw rooster van <strong>{$roosterDatum}</strong> is aangepast.",
                'is_read' => 0,
            ]);
            $notification->save();

            return redirect()->route('roosters.index')->with('success', 'Rooster succesvol bijgewerkt.');
        } catch (\Exception $e) {
            // Log de fout en retourneer naar de vorige pagina met de foutmelding
            return back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het bijwerken van het rooster. Probeer het opnieuw.']);
        }
    }

    public function destroy($id)
    {
        try {
            $rooster = Rooster::findOrFail($id);

            // Sla de gebruikers-ID van het verwijderde rooster op
            $userId = $rooster->user_id;
            $roosterDatum = $rooster->datum->format('d-m-Y');

            // Verwijder het rooster
            $rooster->delete();

            // Maak een nieuwe notificatie aan en stuur deze naar de gebruiker wiens rooster is verwijderd
            $notification = new Notification([
                'user_id' => $userId,
                'title' => 'Rooster Verwijderd',
                'message' => "Je rooster voor datum <strong>{$roosterDatum}</strong> is verwijderd.",
                'is_read' => 0,
            ]);
            $notification->save();

            return response()->json(['success' => 'Rooster is succesvol verwijderd en een notificatie is aangemaakt.']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Er is een fout opgetreden bij het verwijderen van het rooster.'], 500);
        }
    }

    public function requestSwap(Request $request, $id)
    {
        try {
            $rooster = Rooster::findOrFail($id);
            $roosterDate = $rooster->datum;

            // Haal alle gebruikers op
            $users = User::all();

            // Haal de ID van de geselecteerde gebruiker op (dit kan bijvoorbeeld uit een formulier komen)
            $selectedUserId = $request->input('selectedUser');

            // Haal alle datums op van de geselecteerde gebruiker (behalve het huidige rooster)
            $selectedUserDates = Rooster::where('user_id', $selectedUserId)
                ->where('id', '<>', $id) // Uitsluiten van het huidige rooster
                ->pluck('datum')
                ->unique();

            // Definieer $roosterId
            $roosterId = $rooster->id;

            // Geef de view terug met de juiste variabelen, inclusief $selectedUserId en $selectedUserDates
            return view('roosters.requestSwap', compact('roosterDate', 'users', 'selectedUserDates', 'roosterId', 'selectedUserId'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Voeg hier eventueel foutafhandeling toe, bijvoorbeeld een foutmelding weergeven aan de gebruiker
        }
    }

    public function showSwapRequest(Request $request, $id)
    {
        try {
            $rooster = Rooster::findOrFail($id);

            // Haal de geselecteerde gebruiker ID op uit het request
            $selectedUserId = $request->input('selectedUser');

            // Haal alle datums op van de geselecteerde gebruiker (behalve het huidige rooster)
            $selectedUserDates = Rooster::where('user_id', $selectedUserId)
                ->pluck('datum')
                ->unique();

            // Definieer $roosterId
            $roosterId = $rooster->id;

            // Haal alle gebruikers op (voor de selectbox)
            $users = User::all();

            // Datum van het geselecteerde rooster
            $roosterDate = $rooster->datum;

            // Initialiseer $userDatesCount met de count van $selectedUserDates
            $userDatesCount = count($selectedUserDates);

            // Voeg $userDatesCount toe aan de compact functie
            return view('roosters.swapRequest', compact('rooster', 'users', 'selectedUserDates', 'roosterId', 'selectedUserId', 'roosterDate', 'userDatesCount'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Voer andere logica uit of stuur een foutmelding naar de frontend
        }
    }

    public function getUserDates($selectedUserId)
    {
        try {
            // Haal de geselecteerde gebruiker op
            $selectedUser = User::findOrFail($selectedUserId);

            // Voeg extra logging toe om de query te bekijken
            Log::info('Selected User: ' . $selectedUser->id);
            $query = Rooster::where('user_id', $selectedUser->id);
            Log::info('Query: ' . $query->toSql());

            // Haal de datums op waarop de geselecteerde gebruiker is ingeroosterd
            $userDatesWithTimes = $query->get(['datum', 'start_tijd', 'eind_tijd']);

            // Formatteer de tijden voor elk roosteritem
            $formattedUserDates = $userDatesWithTimes->map(function ($item) {
                $startTime = $item->start_tijd ? Carbon::parse($item->start_tijd)->format('H:i') : 'Geen starttijd';
                $endTime = $item->eind_tijd ? Carbon::parse($item->eind_tijd)->format('H:i') : 'Geen eindtijd';
                return [
                    'datum' => $item->datum,
                    'tijd' => $startTime . ' - ' . $endTime,
                ];
            });

            // Voeg extra logging toe om de resultaten te bekijken
            Log::info('User Dates from Database: ' . $formattedUserDates);

            // Voeg de count van de gevonden datums toe
            $userDatesCount = $formattedUserDates->count();

            // Voeg extra logging toe om het aantal gevonden resultaten te bekijken
            Log::info('Number of results: ' . $userDatesCount);

            // Check if the user has any dates
            if ($userDatesCount > 0) {
                return response()->json(['userDates' => $formattedUserDates, 'userDatesCount' => $userDatesCount]);
            } else {
                Log::info('User has no dates.');
                return response()->json(['error' => 'Selected user has no dates.']);
            }
        } catch (\Exception $e) {
            Log::error('Error in getUserDates: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching user dates.']);
        }
    }

    public function processSwapRequest(Request $request, $id)
    {
        try {
            // Huidig rooster ophalen
            $currentRooster = Rooster::findOrFail($id);
            $currentUserId = $currentRooster->user_id;

            // Geselecteerde gebruiker ID uit het formulier
            $selectedUserId = $request->input('selectedUser');

            // Geselecteerde datums uit het formulier
            $selectedUserDates = $request->input('userDates');

            // Controleer of de swap request is goedgekeurd door een admin
            $approvedByAdmin = $this->isSwapRequestApprovedByAdmin($currentUserId, $selectedUserId, $selectedUserDates);

            // Als de aanvraag niet is goedgekeurd door een admin, toon een foutmelding
            if (!$approvedByAdmin) {
                return back()->withErrors(['error' => 'Swap request must be approved by an admin.']);
            }

            // Begin een database transactie
            DB::beginTransaction();

            try {
                // Huidige gebruiker vervangen door de geselecteerde gebruiker in het oorspronkelijke rooster
                Rooster::where('user_id', $selectedUserId)
                    ->whereIn('datum', $selectedUserDates)
                    ->update(['user_id' => $currentUserId, 'approval_status' => 'approved']);

                // Geselecteerde gebruiker vervangen door de huidige gebruiker in het huidige rooster
                Rooster::where('id', $id)
                    ->update(['user_id' => $selectedUserId, 'approval_status' => 'approved']);

                // Commit de transactie
                DB::commit();

                return redirect()->route('roosters.index')->with('success', 'Swap request processed successfully');
            } catch (\Exception $e) {
                // Rol de transactie terug in geval van een fout
                DB::rollBack();
                throw $e; // Gooi de fout opnieuw om terug te vallen naar het algemene vangblok
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to process swap request. Please try again.']);
        }
    }

    private function isSwapRequestApprovedByAdmin($currentUserId, $selectedUserId, $selectedUserDates)
    {
        // Controleer of de huidige gebruiker een admin is
        $currentUserIsAdmin = DB::table('role_user')
            ->where('user_id', $currentUserId)
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('roles.name', 'admin')
            ->exists();

        // Controleer of de geselecteerde gebruiker een admin is
        $selectedUserIsAdmin = DB::table('role_user')
            ->where('user_id', $selectedUserId)
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('roles.name', 'admin')
            ->exists();

        // Controleer of de aanvraag is goedgekeurd door een admin
        $approvedByAdmin = $currentUserIsAdmin || $selectedUserIsAdmin;

        return $approvedByAdmin;
    }

    // ADMIN --------------------------------------------------------------------------------------------------------------

    public function viewSwapRequests()
    {
        $swapRequests = Rooster::with(['currentUser', 'selectedUser'])
            ->where('approval_status', 'pending')
            ->get();

        return view('admin.roosters.swapRequests', compact('swapRequests'));
    }


    public function approveSwapRequest($id)
    {
        try {
            // Zoek de aanvraag
            $swapRequest = SwapRequest::findOrFail($id);

            // Controleer of de aanvraag is goedgekeurd door een admin
            if ($swapRequest->approval_status !== 'approved') { // Gebruik de juiste veldnaam
                return redirect()->route('admin.roosters.swapRequests')->with('error', 'Cannot approve swap request. The request has not been approved by an admin.');
            }

            // Begin een database transactie
            DB::beginTransaction();

            try {
                // Voer de roosteromwisseling uit als de aanvraag is goedgekeurd
                if ($swapRequest->approval_status == 'approved') { // Gebruik de juiste veldnaam
                    // Haal de roosters op van de betrokken gebruikers
                    $currentUserRooster = Rooster::where('user_id', $swapRequest->user_id)
                        ->where('datum', $swapRequest->datum)
                        ->first();

                    $selectedUserRooster = Rooster::where('user_id', $swapRequest->selected_user_id)
                        ->where('datum', $swapRequest->datum)
                        ->first();

                    // Wissel de roosters om
                    $currentUserRooster->update(['user_id' => $swapRequest->selected_user_id]);
                    $selectedUserRooster->update(['user_id' => $swapRequest->user_id]);
                }

                // Commit de database transactie
                DB::commit();

                // Keer terug naar een overzichtspagina of een andere geschikte locatie
                return redirect()->route('admin.roosters.swapRequests')->with('success', 'Swap request approved and rosters will be swapped upon admin approval.');
            } catch (\Exception $e) {
                // Als er een fout optreedt, rol de transactie terug
                DB::rollBack();

                // Keer terug met een foutmelding
                return redirect()->route('admin.roosters.swapRequests')->with('error', 'Error approving swap request: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Als de aanvraag niet wordt gevonden, stuur een foutmelding
            return redirect()->route('admin.roosters.swapRequests')->with('error', 'Swap request not found.');
        }
    }

    public function rejectSwapRequest($id)
    {
        try {
            $swapRequest = Rooster::findOrFail($id);
            $swapRequest->approval_status = 'rejected';
            $swapRequest->save();

            return redirect()->route('admin.roosters.swapRequests')->with('success', 'Swap request rejected successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reject swap request. Please try again.']);
        }
    }

    public function updateApprovalStatus(Request $request, $id, $status)
    {
        try {
            $rooster = Rooster::findOrFail($id);
            $rooster->approval_status = $status; // Zorg ervoor dat de status tussen enkele aanhalingstekens staat
            $rooster->save();

            return response()->json(['message' => 'Approval status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update approval status.']);
        }
    }

    public function getRoosterData(Request $request)
    {
        $data = Rooster::with('user')->get();

        $data->transform(function ($item) {
            $item->user_name = $item->user->name;
            return $item;
        });

        return response()->json($data);
    }
}
