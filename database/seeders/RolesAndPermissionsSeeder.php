<?php

namespace Database\Seeders;

// database/seeders/RolesAndPermissionsSeeder.php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Maak een rol aan
        $role = Role::create(['name' => 'werkgever']);

        // Maak een machtiging aan
        $permission = Permission::create(['name' => 'manage_roosters']);

        // Ken de machtiging toe aan de rol
        $role->givePermissionTo($permission);
    }
}

