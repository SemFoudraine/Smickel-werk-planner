<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Verlofaanvraag;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Notification;

class VerlofaanvraagController extends Controller
{
    public function index()
    {
        // Check of de ingelogde gebruiker een admin is
        if (Auth::user()->hasRole('admin')) {
            // Haal alle verlofaanvragen op voor een admin
            $verlofaanvragen = Verlofaanvraag::with('user')->get();
        } else {
            // Haal alleen verlofaanvragen op van de ingelogde gebruiker voor niet-admins
            $verlofaanvragen = Verlofaanvraag::with('user')
                ->where('user_id', Auth::id())
                ->get();
        }

        return view('verlof.index', compact('verlofaanvragen'));
    }

    public function indexApi()
    {
        if (Auth::user()->hasRole('admin')) {
            // Haal alle verlofaanvragen op voor een admin
            $verlofaanvragen = Verlofaanvraag::with('user')->get();
        } else {
            // Haal alleen verlofaanvragen op van de ingelogde gebruiker voor niet-admins
            $verlofaanvragen = Verlofaanvraag::with('user')
                ->where('user_id', Auth::id())
                ->get();
        }

        return response()->json($verlofaanvragen);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Verlofaanvraag store request: ', $request->all());

            $request->validate([
                'datum' => 'required|date',
                'eind_datum' => 'nullable|date|after_or_equal:datum',
                'reden' => 'nullable',
            ]);

            $verlofaanvraag = Verlofaanvraag::create([
                'user_id' => Auth::id(),
                'datum' => $request->input('datum'),
                'eind_datum' => $request->input('eind_datum'),
                'reden' => $request->input('reden'),
            ]);

            Log::info('Verlofaanvraag succesvol toegevoegd', ['verlofaanvraag' => $verlofaanvraag]);

            return response()->json(['message' => 'Verlofaanvraag succesvol toegevoegd', 'verlofaanvraag' => $verlofaanvraag], 201);
        } catch (\Exception $e) {
            Log::error('Error adding verlofaanvraag: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to add verlofaanvraag', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $verlofaanvraag = Verlofaanvraag::findOrFail($id);
            $verlofaanvraag->delete();

            return response()->json(['message' => 'Verlofaanvraag succesvol verwijderd'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting verlofaanvraag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete verlofaanvraag', 'message' => $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        try {
            $verlofaanvraag = Verlofaanvraag::findOrFail($id);
            $verlofaanvraag->update(['status' => 'Goedgekeurd']);

            $datum = Carbon::createFromFormat('Y-m-d', $verlofaanvraag->datum);

            Notification::create([
                'user_id' => $verlofaanvraag->user_id,
                'title' => 'Verlof',
                'message' => 'Jouw verlofaanvraag voor <strong>' . $datum->format('d-m-Y') . '</strong> is goedgekeurd.',
                'is_read' => 0,
            ]);

            return response()->json(['message' => 'Verlofaanvraag is goedgekeurd'], 200);
        } catch (\Exception $e) {
            Log::error('Error approving verlofaanvraag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to approve verlofaanvraag', 'message' => $e->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        try {
            $verlofaanvraag = Verlofaanvraag::findOrFail($id);
            $verlofaanvraag->update(['status' => 'Afgekeurd']);

            $datum = Carbon::createFromFormat('Y-m-d', $verlofaanvraag->datum);

            Notification::create([
                'user_id' => $verlofaanvraag->user_id,
                'title' => 'Verlof',
                'message' => 'Jouw verlofaanvraag voor <strong>' . $datum->format('d-m-Y') . '</strong> is afgekeurd.',
                'is_read' => 0,
            ]);

            return response()->json(['message' => 'Verlofaanvraag is afgekeurd'], 200);
        } catch (\Exception $e) {
            Log::error('Error rejecting verlofaanvraag: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reject verlofaanvraag', 'message' => $e->getMessage()], 500);
        }
    }
}
