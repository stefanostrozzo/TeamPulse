<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

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
     * Test if an authenticated user can successfully create a new team.
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

        // Verify the user's current context is updated to the new team
        $this->assertEquals($team->id, $user->fresh()->current_team_id);
    }

    /**
     * Test if a team owner can remove a member.
     */
    public function test_team_owner_can_remove_a_member(): void
    {
        // 1. Setup entities
        $owner = User::factory()->create();
        $team = Team::create(['name' => 'Core Team']);
        $member = User::factory()->create();

        // 2. Setup Team Relationships
        $owner->teams()->attach($team->id, ['role' => 'owner']);
        $owner->update(['current_team_id' => $team->id]);
        $member->teams()->attach($team->id, ['role' => 'member']);

        // 3. Clear cache INIZIALE
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 4. Setup Spatie permissions CORRETTO
        // Crea ruolo e permesso se non esistono
        $role = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'remove members', 'guard_name' => 'web']);

        // Assegna permesso al ruolo
        if (!$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }

        // 5. Assegna il ruolo all'utente CON contesto del team
        // VERSIONE 1: Usando direttamente il modello pivot
        $owner->roles()->attach($role->id, ['team_id' => $team->id]);

        // OPPURE VERSIONE 2: Se vuoi usare i metodi Spatie
        // setPermissionsTeamId($team->id);
        // $owner->assignRole($role);
        // setPermissionsTeamId(null);

        // 6. Clear cache FINALE
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 7. DEBUG: Verifica i permessi CON il contesto del team
        setPermissionsTeamId($team->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        setPermissionsTeamId(null);
        \DB::connection()->disableQueryLog();
        // 8. Execute Request
        $response = $this->actingAs($owner)->delete(route('teams.members.remove', [
            'team' => $team,
            'user' => $member
        ]));


        // 9. Assertions
        $response->assertStatus(302);
        $response->assertRedirect(route('home', ['tab' => 'teams']));

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id
        ]);
    }
}
