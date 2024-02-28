<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class RoleController extends Controller
{
    public function assignRoleToUser($userId)
{
    // Zoek de gebruiker
    $user = User::find($userId);

    // Zoek een specifieke rol
    $role = Role::where('name', 'werkgever')->first();

    // Wijs de gevonden rol toe aan de gebruiker
    $user->roles()->attach($role);

    return redirect()->back()->with('success', 'Rol is toegewezen aan gebruiker');
}
}
