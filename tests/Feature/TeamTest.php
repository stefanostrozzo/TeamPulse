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
        $this->assertTrue($user->fresh()->teams->contains($team));

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

        // 3. Set Spatie team context and assign the owner role properly
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        setPermissionsTeamId($team->id);

        // The PermissionSeeder already created the 'owner' role with all permissions.
        // We just need to assign it to the user in the correct team context.
        $owner->assignRole('owner');

        // 4. Verify permissions are working before the request
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $owner->unsetRelation('roles');
        $owner->unsetRelation('permissions');

        // Direct assertion that the user has the permission
        $this->assertTrue(
            $owner->can('remove members'),
            'Owner should have "remove members" permission in team context'
        );

        // 5. Execute Request
        $response = $this->actingAs($owner)->delete(route('teams.members.remove', [
            'team' => $team,
            'user' => $member
        ]));

        // 6. Assertions
        $response->assertStatus(302);

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id
        ]);
    }
}
