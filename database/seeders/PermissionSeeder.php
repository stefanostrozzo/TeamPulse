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
        //Create permissions
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

        //Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        //Assign permissions to roles
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
    }
} 