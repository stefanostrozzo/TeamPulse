<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crea i permessi
        $permissions = [
            'manage users',
            'manage roles',
            'view users',
            'edit users',
            'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crea i ruoli
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assegna permessi ai ruoli
        $adminRole->givePermissionTo([
            'manage users',
            'manage roles',
            'view users',
            'edit users',
            'delete users',
        ]);

        $userRole->givePermissionTo([
            'view users',
        ]);

        // Assegna ruolo admin al primo utente (se esiste)
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('admin');
        }
    }
} 