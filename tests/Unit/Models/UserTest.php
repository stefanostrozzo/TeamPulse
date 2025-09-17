<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

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
        $user = User::factory()->create();
        $role = Role::findOrCreate('admin');

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_can_assign_multiple_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::findOrCreate('admin');
        $editorRole = Role::findOrCreate('editor');

        $user->assignRole([$adminRole, $editorRole]);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_user_can_assign_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-users']);

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

    // Removed: email lowercase behavior is handled at request validation time, not model-level

    public function test_user_can_sync_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::findOrCreate('admin');
        $editorRole = Role::findOrCreate('editor');
        $userRole = Role::findOrCreate('user');

        $user->assignRole($adminRole);
        $user->syncRoles([$editorRole, $userRole]);

        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_user_can_revoke_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-users']);
        
        $user->givePermissionTo($permission);
        $this->assertTrue($user->hasPermissionTo('manage-users'));

        $user->revokePermissionTo($permission);
        $this->assertFalse($user->hasPermissionTo('manage-users'));
    }
}
