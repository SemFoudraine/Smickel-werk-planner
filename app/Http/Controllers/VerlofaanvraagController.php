<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Verlofaanvraag;
use App\Models\User;
use HasFactory, Notifiable;
use App\Models\Notification;
use Carbon\Carbon;
use App\Models\Role;

class VerlofaanvraagController extends Controller
{
    public function index()
    {
        // Check of de ingelogde gebruiker een admin is
        if (auth()->User()->hasRole('admin')) {
            // Haal alle verlofaanvragen op voor een admin
            $verlofaanvragen = Verlofaanvraag::with('user')->get();
        } else {
            // Haal alleen verlofaanvragen op van de ingelogde gebruiker voor niet-admins
            $verlofaanvragen = Verlofaanvraag::with('user')
                ->where('user_id', auth()->id())
                ->get();
        }

        return view('verlof.index', compact('verlofaanvragen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'datum' => 'required|date',
            'eind_datum' => 'nullable|date|after_or_equal:datum', // Zorg ervoor dat eind_datum na datum is
            'reden' => 'nullable',
        ]);

        Verlofaanvraag::create([
            'user_id' => auth()->user()->id,
            'datum' => $request->input('datum'),
            'eind_datum' => $request->input('eind_datum'), // Sla de eind_datum op
            'reden' => $request->input('reden'),
        ]);

        return redirect()->route('verlofaanvragen.index');
    }

    public function destroy($id)
    {
        $verlofaanvraag = Verlofaanvraag::findOrFail($id);
        $verlofaanvraag->delete();

        return redirect()->route('verlofaanvragen.index')->with('success', 'Verlofaanvraag succesvol verwijderd');
    }


    public function approve($id)
    {
        $verlofaanvraag = Verlofaanvraag::findOrFail($id);
        $verlofaanvraag->update(['status' => 'Goedgekeurd']);

        $datum = Carbon::createFromFormat('Y-m-d', $verlofaanvraag->datum);

        Notification::create([
            'user_id' => $verlofaanvraag->user_id,
            'title' => 'Verlof',
            'message' => 'Jouw verlofaanvraag voor <strong>' . $datum->format('d-m-Y') . '</strong> is goedgekeurd.',
            'is_read' => 0,
        ]);

        return redirect()->route('verlofaanvragen.index')->with('success', 'Verlofaanvraag is goedgekeurd.');
    }

    public function reject($id)
    {
        $verlofaanvraag = Verlofaanvraag::findOrFail($id);
        $verlofaanvraag->update(['status' => 'Afgekeurd']);

        $datum = Carbon::createFromFormat('Y-m-d', $verlofaanvraag->datum);

        Notification::create([
            'user_id' => $verlofaanvraag->user_id,
            'title' => 'Verlof',
            'message' => 'Jouw verlofaanvraag voor <strong>' . $datum->format('d-m-Y') . '</strong> is afgekeurd.',
            'is_read' => 0,
        ]);

        return redirect()->route('verlofaanvragen.index')->with('error', 'Verlofaanvraag is afgekeurd.');
    }
}
