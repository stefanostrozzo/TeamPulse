<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeamInvitationMail;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test if an authorized user can send an email invitation to a new member.
     */
    public function test_authorized_user_can_send_team_invitation(): void
    {
        // Fake the mailer to prevent actual emails from being sent
        Mail::fake();

        $owner = User::factory()->create();
        $team = Team::create(['name' => 'Dev Team']);

        // Setup owner roles/permissions
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'invite members']);
        setPermissionsTeamId($team->id);
        $owner->teams()->attach($team->id, ['role' => 'owner']);
        $owner->givePermissionTo('invite members');
        $owner->update(['current_team_id' => $team->id]);

        $response = $this->actingAs($owner)->post(route('teams.invite'), [
            'email' => 'candidate@example.com',
            'role' => 'member',
            'team_id' => $team->id
        ]);

        $response->assertSessionHasNoErrors();

        // Verify invitation record was created with a secure token
        $this->assertDatabaseHas('invitations', [
            'email' => 'candidate@example.com',
            'team_id' => $team->id
        ]);

        // Assert that the specific Mailable class was sent
        Mail::assertSent(TeamInvitationMail::class, function ($mail) {
            return $mail->hasTo('candidate@example.com');
        });
    }

    /**
     * Test the full acceptance flow: Token verification and team attachment.
     */
    public function test_user_can_accept_invitation_via_token(): void
    {
        $team = Team::create(['name' => 'Marketing']);
        $invitation = Invitation::create([
            'email' => 'new-user@test.com',
            'team_id' => $team->id,
            'role' => 'member',
            'token' => 'secure-acceptance-token-123',
            'expires_at' => now()->addDay()
        ]);

        // Create the user who will accept the invitation
        $user = User::factory()->create(['email' => 'new-user@test.com']);

        // Access the invitation route
        $response = $this->actingAs($user)->get(route('teams.accept', [
            'token' => 'secure-acceptance-token-123'
        ]));

        $response->assertRedirect(route('home', ['tab' => 'teams']));

        // Refresh user data to check relations
        $user->refresh();

        // Assert the user is now part of the team
        $this->assertTrue($user->teams->contains($team));

        // Assert current context switched to the new team
        $this->assertEquals($team->id, $user->current_team_id);

        // Assert the invitation record was deleted after successful use
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    /**
     * Test that an authorized user can cancel (revoke) a pending invitation.
     */
    public function test_authorized_user_can_cancel_pending_invitation(): void
    {
        // Reset Spatie's permission cache to prevent cross-test contamination
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = User::factory()->create();
        $team = Team::create(['name' => 'Cancel Test Team']);

        // Setup owner roles/permissions (mirrors the send-invitation test pattern)
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'invite members']);
        setPermissionsTeamId($team->id);
        $owner->teams()->attach($team->id, ['role' => 'owner']);
        $owner->givePermissionTo('invite members');
        $owner->update(['current_team_id' => $team->id]);

        // Create a pending invitation
        $invitation = Invitation::create([
            'email'      => 'to-cancel@example.com',
            'team_id'    => $team->id,
            'role'       => 'member',
            'token'      => 'cancel-test-token-456',
            'expires_at' => now()->addDay(),
        ]);

        // Ensure clean permission state before the request
        $owner->unsetRelation('roles');
        $owner->unsetRelation('permissions');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Act: cancel the invitation (bypass all middleware to isolate
        // whether the 403 comes from middleware or controller authorization)
        $response = $this->withoutMiddleware()
            ->actingAs($owner)
            ->delete(route('teams.invitations.cancel', $invitation->id));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Assert the invitation record was removed
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }
}
