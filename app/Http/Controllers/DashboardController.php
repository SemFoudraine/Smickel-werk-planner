<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Rooster;
use App\Models\User;
use App\Models\Verlofaanvraag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Initialiseer de variabelen
        $totalHoursThisMonth = 0;
        $totalHoursThisWeek = 0;
        $roostersThisWeek = collect();
        $adminDashboard = [];

        if (Auth::check()) {
            $userId = Auth::id();
            $currentWeekStart = Carbon::now()->startOfWeek();
            $currentWeekEnd = Carbon::now()->endOfWeek();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            // Haal roosters van deze week op voor de ingelogde gebruiker
            $roostersThisWeek = Rooster::where('user_id', $userId)
                ->whereBetween('datum', [$currentWeekStart, $currentWeekEnd])
                ->get();

            // Bereken de totale uren van deze week
            $totalHoursThisWeek = $roostersThisWeek->reduce(function ($carry, $rooster) {
                $startTijd = Carbon::parse($rooster->start_tijd);
                $eindTijd = Carbon::parse($rooster->eind_tijd);
                return $carry + $eindTijd->diffInHours($startTijd);
            }, 0);

            // Bereken de totale uren van deze maand voor de ingelogde gebruiker
            $totalHoursThisMonth = Rooster::where('user_id', $userId)
                ->whereBetween('datum', [$startOfMonth, $endOfMonth])
                ->sum('duur_in_uren');

            if (Auth::user()->hasRole('admin')) {
                // Admin-specifieke statistieken
                $adminDashboard['totalHoursThisMonth'] = Rooster::whereBetween('datum', [$startOfMonth, $endOfMonth])->sum('duur_in_uren');
                $adminDashboard['totalHoursThisWeek'] = Rooster::whereBetween('datum', [$currentWeekStart, $currentWeekEnd])->sum('duur_in_uren');
                $adminDashboard['totalEmployees'] = User::count();
                $adminDashboard['recentlyRegisteredUsers'] = User::latest()->take(5)->get();
                $adminDashboard['leaveRequestCounts'] = Verlofaanvraag::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->get();

                // Aangepaste query om de meest actieve gebruikers te tonen op basis van het totale aantal gewerkte uren in de huidige maand
                $adminDashboard['mostActiveUsers'] = Rooster::select('user_id', DB::raw('SUM(duur_in_uren) as total_hours'))
                    ->whereBetween('datum', [$startOfMonth, $endOfMonth])
                    ->groupBy('user_id')
                    ->orderBy('total_hours', 'DESC')
                    ->take(5)
                    ->with('user')
                    ->get();

                $adminDashboard['averageHoursWorked'] = Rooster::avg('duur_in_uren');
            }
        }

        return view('dashboard', compact('roostersThisWeek', 'totalHoursThisMonth', 'totalHoursThisWeek', 'adminDashboard'));
    }
}
