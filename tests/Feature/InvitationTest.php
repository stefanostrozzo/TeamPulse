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
}
