<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Facades\Gate;

class TeamTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie's internal permission cache to ensure a clean state for each test
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure essential roles and permissions exist in the database
        Role::firstOrCreate(['name' => 'owner']);
        Permission::firstOrCreate(['name' => 'remove members']);
    }

    /**
     * Test if an authenticated user can successfully create a new team and is the owner of the newly created team
     */
    public function test_authenticated_user_can_create_a_team(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('teams.store'), [
            'name' => 'Innovation Squad'
        ]);

        // Assert redirect to home (Dashboard)
        $response->assertStatus(302);

        // Verify the team exists in the database
        $this->assertDatabaseHas('teams', ['name' => 'Innovation Squad']);

        $team = Team::where('name', 'Innovation Squad')->first();

        // Verify the creator is automatically attached to the team
        $this->assertTrue($user->teams->contains($team));
    }

    /**
     * Test if a team owner can remove a member.
     */
    public function test_team_owner_can_remove_a_member(): void
    {
        $owner = User::factory()->create();
        $team = Team::create(['name' => 'Core Team']);
        $member = User::factory()->create();

        // 1. Relazioni standard Laravel
        $owner->teams()->attach($team->id, ['role' => 'owner']);
        $member->teams()->attach($team->id, ['role' => 'member']);
        $owner->update(['current_team_id' => $team->id]);

        // 2. Setup Ruoli e Permessi
        $role = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'remove members', 'guard_name' => 'web']);
        $role->syncPermissions([$permission]);

        // 3. FORZA l'assegnazione del ruolo al Team direttamente nel DB
        // Questo evita ogni problema di cache di Spatie durante il setup
        \DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $owner->id,
            'team_id' => $team->id,
        ]);

        setPermissionsTeamId($team->id);

        // 4. Reset totale della cache prima della chiamata
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $owner->refresh();
        $owner->unsetRelation('roles');
        $owner->unsetRelation('permissions');

        // 5. Esegui la chiamata
        $response = $this->actingAs($owner, 'web')
            ->from(route('home'))
            ->delete(route('teams.members.remove', [
                'team' => $team->id,
                'user' => $member->id
            ]));

        // 6. Assertions
        $response->assertStatus(302);
        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id
        ]);
    }
}
