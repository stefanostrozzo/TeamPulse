<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper: create a team and set the Spatie permission context.
     * Required because the project uses Spatie's teams feature.
     */
    private function createTeamContext(): Team
    {
        $team = Team::create(['name' => 'Test Team']);
        setPermissionsTeamId($team->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return $team;
    }

    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_has_roles_trait()
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'hasRole'));
        $this->assertTrue(method_exists($user, 'assignRole'));
        $this->assertTrue(method_exists($user, 'removeRole'));
    }

    public function test_user_can_assign_role()
    {
        $this->createTeamContext();

        $user = User::factory()->create();
        $role = Role::findOrCreate('admin', 'web');

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_can_assign_multiple_roles()
    {
        $this->createTeamContext();

        $user = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $editorRole = Role::findOrCreate('editor', 'web');

        $user->assignRole([$adminRole, $editorRole]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_user_can_assign_permission()
    {
        $this->createTeamContext();

        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'manage-users', 'guard_name' => 'web']);

        $user->givePermissionTo($permission);

        $this->assertTrue($user->hasPermissionTo('manage-users'));
    }

    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => 'plaintext'
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(password_verify('plaintext', $user->password));
    }

    public function test_user_can_sync_roles()
    {
        $this->createTeamContext();

        $user = User::factory()->create();
        $adminRole = Role::findOrCreate('admin', 'web');
        $editorRole = Role::findOrCreate('editor', 'web');
        $userRole = Role::findOrCreate('user', 'web');

        $user->assignRole($adminRole);
        $user->syncRoles([$editorRole, $userRole]);

        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_user_can_revoke_permission()
    {
        $this->createTeamContext();

        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'manage-users', 'guard_name' => 'web']);

        $user->givePermissionTo($permission);
        $this->assertTrue($user->hasPermissionTo('manage-users'));

        $user->revokePermissionTo($permission);
        $this->assertFalse($user->hasPermissionTo('manage-users'));
    }
}
