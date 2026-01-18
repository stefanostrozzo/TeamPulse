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
            // Team Management
            'invite members',
            'remove members',
            'create team',
            'delete team',
            'change member roles',
            'manage team settings',

            // Project Management
            'create projects',
            'edit projects',
            'delete projects',
            'view all projects',

            // Task & comment Management
            'create tasks',
            'edit tasks',
            'delete tasks',
            'assign tasks',
            'move tasks',
            'post comments',
            'manage comments',

            // Communication & Collaboration
            'manage watchers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        //SuperAdmin
        $superAdmin = Role::firstOrCreate(['name' => 'superadmin']);

        //Owner role
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->givePermissionTo(Permission::all());

        //Manager role
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'invite members',
            'create projects',
            'edit projects',
            'view all projects',
            'create tasks',
            'edit tasks',
            'assign tasks',
            'move tasks',
            'post comments',
            'manage comments',
            'manage watchers',
            'change member roles',
            'delete tasks'
        ]);

        //Member role
        $member = Role::firstOrCreate(['name' => 'member']);
        $member->givePermissionTo([
            'create tasks',
            'edit tasks',
            'delete tasks',
            'move tasks',
            'post comments',
        ]);

        //Guest role (can only view)
        $guest = Role::firstOrCreate(['name' => 'guest']);
        $guest->givePermissionTo();
    }
}
