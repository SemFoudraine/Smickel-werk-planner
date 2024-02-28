<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Rooster;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Initialiseer de variabelen buiten de Auth::check()
        $totalHoursThisMonth = 0;
        $totalHoursThisWeek = 0;

        if (Auth::check()) {
            $userId = Auth::id();
            $currentWeekStart = Carbon::now()->startOfWeek();
            $currentWeekEnd = Carbon::now()->endOfWeek();

            $roostersThisWeek = Rooster::where('user_id', $userId)
                ->whereBetween('datum', [$currentWeekStart, $currentWeekEnd])
                ->get();

            // Bereken de totale uren van deze week
            $totalHoursThisWeek = $roostersThisWeek->reduce(function ($carry, $rooster) {
                $startTijd = Carbon::parse($rooster->start_tijd);
                $eindTijd = Carbon::parse($rooster->eind_tijd);
                return $carry + $eindTijd->diffInHours($startTijd);
            }, 0);

            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $totalHoursThisMonth = Rooster::where('user_id', $userId)
                ->whereBetween('datum', [$startOfMonth, $endOfMonth])
                ->sum('duur_in_uren'); // Zorg ervoor dat 'duur_in_uren' overeenkomt met de kolomnaam in je database
        }

        return view('dashboard', [
            'roostersThisWeek' => $roostersThisWeek ?? [], // Gebruik null coalescing operator om lege array als standaard te stellen
            'totalHoursThisMonth' => $totalHoursThisMonth,
            'totalHoursThisWeek' => $totalHoursThisWeek, // Voeg deze regel toe om de uren van deze week door te geven aan de view
        ]);
    }
}
