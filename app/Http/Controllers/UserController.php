<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rooster;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Task;
use App\Models\Notification;
use Carbon\Carbon;

class UserController extends Controller
{
    public function showProfile($id)
    {
        $user = User::findOrFail($id);

        // Return a view and pass the user data to it
        return view('users.profile', compact('user'));
    }

    public function hasRole(User $user, string $roleName): bool
    {
        return $user->roles()->where('name', $roleName)->exists();
    }

    public function showAssignAdminRoleForm($userId)
    {
        $user = User::findOrFail($userId);
        return view('assign_admin_role_form', compact('user'));
    }

    public function assignAdminRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Voeg de gebruiker toe aan de adminrol
        $user->assignRole('admin');

        return redirect()->route('users.index')->with('success', 'De adminrol is succesvol toegekend aan de gebruiker.');
    }

    public function index()
    {
        $users = User::all();
        $tasks = Task::all(); // Zorg ervoor dat je dit toevoegt

        // Geef zowel users als tasks door naar de view
        return view('users.index', compact('users', 'tasks'));
    }


    public function removeFromRooster($roosterId)
    {
        // Hier vinden we het rooster op basis van het rooster ID
        $roosterEntry = Rooster::find($roosterId);

        // Controleren of het rooster bestaat
        if (!$roosterEntry) {
            return redirect()->back()->with('error', 'Rooster entry niet gevonden.');
        }

        // Aanvullende controle of de gebruiker admin-rechten heeft
        $user = auth()->user();
        if (!$this->hasRole($user, 'admin')) {
            abort(403, 'Dit is niet toegestaan.');
        }

        // Gegevens voor de notificatie
        $datum = Carbon::parse($roosterEntry->datum);
        $starttijd = Carbon::parse($roosterEntry->start_tijd);
        $eindtijd = Carbon::parse($roosterEntry->eind_tijd);

        $geformatteerdeDatum = $datum->format('d-m-Y');
        $geformatteerdeStartTijd = $starttijd->format('H:i');
        $geformatteerdeEindTijd = $eindtijd->format('H:i');

        // Gebruik de user_id van het rooster
        $userId = $roosterEntry->user_id;

        // Verwijder het rooster entry als alles in orde is
        $roosterEntry->delete();

        // Maak een nieuwe notificatie aan en stuur deze naar de gebruiker wiens rooster is verwijderd
        $notification = new Notification([
            'user_id' => $userId, // Gebruik de user_id van het rooster
            'title' => 'Rooster wijziging',
            'message' => 'Je rooster van <strong>' . $geformatteerdeDatum .
                '</strong> van <strong>' . $geformatteerdeStartTijd .
                '</strong> tot <strong>' . $geformatteerdeEindTijd . '</strong>' . ' is verwijderd.',
            'is_read' => 0,
        ]);
        $notification->save();

        return redirect()->back()->with('success', 'Rooster entry succesvol verwijderd.');
    }

    public function editTimes($userId)
    {
        $user = User::findOrFail($userId);

        if (!$this->hasRole($user, 'admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Logica om de tijden aan te passen

        return redirect()->back()->with('success', 'Tijden succesvol aangepast.');
    }

    public function destroy($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('admin')) {
            return redirect()->back()->with('error', 'Kan de admin-gebruiker niet verwijderen.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Gebruiker succesvol verwijderd.');
    }

    public function roles($userId)
    {
        $user = User::findOrFail($userId);
        $roles = $user->roles; // Dit veronderstelt dat je een relatie hebt gedefinieerd tussen gebruikers en rollen

        return view('roles.index', compact('user', 'roles'));
    }

    public function assignRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $roleName = $request->input('role');

        $role = Role::where('name', $roleName)->firstOrFail();
        $user->roles()->attach($role);

        return redirect()->back()->with('success', 'Rol succesvol toegevoegd.');
    }

    public function removeRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $roleName = $request->input('role');

        $role = Role::where('name', $roleName)->firstOrFail();
        $user->roles()->detach($role);

        return redirect()->back()->with('success', 'Rol succesvol verwijderd.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        // Geef de gebruiker door naar de view om te bewerken
        return view('users.edit', compact('user'));
    }


    public function showTasks()
    {
        $tasks = Task::all(); // Veronderstelt dat je modelnaam Task is
        return view('tasks.index', compact('tasks'));
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'naam' => 'required|string|max:255',
        ]);

        Task::create([
            'naam' => $request->naam,
        ]);

        return back()->with('success', 'Taak succesvol toegevoegd.');
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        // Valideer de invoer van de gebruiker
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Maak de gebruiker aan in de database
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // Wachtwoord hashen
        ]);

        // Optioneel: de gebruiker direct inloggen of een andere actie uitvoeren

        // Redirect naar een pagina (bijv. gebruikerslijst) met een succesmelding
        return redirect()->route('admin.index')->with('success', 'Gebruiker succesvol toegevoegd.');
    }

    public function editTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        return view('tasks.edit', compact('task'));
    }

    public function updateTask(Request $request, $taskId)
    {
        $request->validate([
            'naam' => 'required|string|max:255',
        ]);

        $task = Task::findOrFail($taskId);
        $task->update([
            'naam' => $request->naam,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Taak succesvol bijgewerkt.');
    }

    public function destroyTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->delete();

        return back()->with('success', 'Taak succesvol verwijderd.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->birthdate = $request->input('birthdate');
        $user->save();

        return redirect()->route('users.index')->with('success', 'Gebruiker succesvol bijgewerkt.');
    }
}
