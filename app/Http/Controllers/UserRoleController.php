<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserRoleController extends Controller
{
    public function assignAdminRoleToUserView($userId)
    {
        $user = User::find($userId);
        if ($user) {
            return view('admin.assign-role', compact('user'));
        }
        return "User not found";
    }

    public function assignAdminRoleToUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $role = Role::where('name', 'admin')->first();
            if ($role) {
                $user->roles()->attach($role);
                return "Admin role assigned to user with ID: $userId";
            }
            return "Admin role not found";
        }
        return "User not found";
    }
}
