<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Invitation;
use App\Mail\TeamInvitationMail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;

    /**
     * Switch the user's current context to a different team.
     * his method updates the 'current_team_id' on the user record,
     * which triggers the SetTeamContext middleware on the next request.
     * @param Request $request
     * @param Team $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request, Team $team)
    {
        // Security check: Ensure the user is actually a member of the team they are switching to
        if (!$request->user()->teams->contains($team)) {
            abort(403, 'Non appartieni a questo team');
        }

        // Update the pointer for the current active team
        $request->user()->update([
            'current_team_id' => $team->id
        ]);

        // Redirect back to the dashboard with the new context active
        return redirect()->route('home', ['tab' => 'teams'])->with('status', "Nuovo team attivo: {$team->name}");
    }

    /**
     * Store a newly created team in the database.
     * When a team is created, the creator is automatically attached as an 'owner'
     * and the new team is set as their current context.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|mixed
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // 1. Create the team
            $team = Team::create($validated);

            // 2. Attach the current user as the Owner in the pivot table
            $request->user()->teams()->attach($team->id, ['role' => 'owner']);

            // 3. Set the new team as the user's active team
            $request->user()->update(['current_team_id' => $team->id]);

            // 4. Register the permissions for Spatie
            setPermissionsTeamId($team->id);
            $request->user()->assignRole('owner');

            return redirect()->route('home', ['tab' => 'teams'])
                ->with('status', 'Team creato correttamente!');
        });
    }

    /**
     * Updates the team name
     * @param Request $request
     * @param Team $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        //1. Update the team
        $team->update($validated);

        return redirect()->route('home', ['tab' => 'teams'])
            ->with('status', 'Team modificato correttamente!');
    }


    /**
     * @param $teamId
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($teamId, Request $request){

        setPermissionsTeamId($teamId);
        $this->authorize('delete team');

        $team = Team::findOrFail($teamId);
        $team->delete();

        $activeTab = $request->get('tab', 'teams');
        return redirect()->route('home', ['tab' => $activeTab])
            ->with('status', 'Team successfully deleted!');
    }

    /**
     * Create a team invitation and send it via email.
     * This method handles invitations for both existing and non-existing users.
     * It generates a secure token and stores the invitation context.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addMember(Request $request)
    {
        $teamId = $request->input('team_id') ?? $request->user()->current_team_id;
        $team = Team::findOrFail($teamId);

        setPermissionsTeamId($teamId);

        // 1. Authorization check using Spatie/Gate
        $this->authorize('invite members');

        // 2. Validation (Note: we removed 'exists:users' because the user might be new)
        $validated = $request->validate([
            'email' => 'required|email',
            'role'  => 'required|string|in:manager,member,guest',
        ]);

        // 3. Prevent duplicate active invitations for the same team
        $existingInvitation = \App\Models\Invitation::where('team_id', $team->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existingInvitation) {
            return redirect()->route('home', ['tab' => 'teams'])->withErrors(['email' => 'Un invito è già stato inoltrato di recente...']);
        }

        // 4. Check if the user is already a member of the team
        $existingUser = \App\Models\User::where('email', $validated['email'])->first();
        if ($existingUser && $team->users()->where('user_id', $existingUser->id)->exists()) {
            return redirect()->route('home', ['tab' => 'teams'])->withErrors(['email' => 'L\'utente selezionato appartiene già al team']);
        }

        // 5. Create the invitation record
        $invitation = Invitation::create([
            'email'      => $validated['email'],
            'team_id'    => $team->id,
            'role'       => $validated['role'],
            'token'      => \Illuminate\Support\Str::random(64),
            'expires_at' => now()->addDay(), // Invitation expires in 1 day
        ]);

        // 6. Send the invitation email
        \Illuminate\Support\Facades\Mail::to($validated['email'])
            ->send(new TeamInvitationMail($invitation));

        return redirect()->route('home', ['tab' => 'teams'])
            ->with('status', 'Invito inoltrato con successo!');
    }

    /**
     * Handle the invitation acceptance.
     * Verifies the token and links the user to the team
     * @param Request $request
     * @param $token
     * @return \Illuminate\Http\RedirectResponse|mixed
     * @throws \Throwable
     */
    public function acceptInvitation(Request $request, $token)
    {
        // 1. Find the invitation or fail
        $invitation = Invitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // 2. Check if the user is already registered with this email
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            // If the user doesn't exist, redirect to registration
            // passing the token to handle it after signup
            return redirect()->route('register', ['token' => $token])
                ->with('status', 'Please register to join the team.');
        }

        // 3. If user exists, attach them to the team
        return DB::transaction(function () use ($invitation, $user) {
            // Check if already in team to avoid duplicates
            if (!$user->teams->contains($invitation->team_id)) {
                $user->teams()->attach($invitation->team_id, ['role' => $invitation->role]);
            }

            setPermissionsTeamId($invitation->team_id);
            $user->assignRole($invitation->role);

            // Set this team as current
            $user->update(['current_team_id' => $invitation->team_id]);

            // Delete the used invitation
            $invitation->delete();

            return redirect()->route('home',['tab' => 'teams'])->with('status', 'Benvenuto nel team!');
        });
    }

    /**
     * Removes member from the team
     * @param Team $team
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeMember(Team $team, User $user)
    {
        setPermissionsTeamId($team->id);

        if (auth()->check()) {
            auth()->user()->unsetRelation('roles');
            auth()->user()->unsetRelation('permissions');

        }

        $this->authorize('remove members');

        $isOwner = $team->users()->where('user_id', $user->id)->where('role', 'owner')->exists();

        if ($isOwner && $team->users()->where('role', 'owner')->count() <= 1) {
            return back()->withErrors(['error' => 'Il team deve avere almeno un proprietario.']);
        }

        $team->users()->detach($user->id);

        if ($user->current_team_id === $team->id) {
            $user->update(['current_team_id' => $user->teams()->first()?->id]);
        }

        return back()->with('status', 'Utente rimosso dal team!');
    }

    /**
     * Update member roles in bulk for a specific team.
     * * @param Request $request
     * @param Team $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMemberRole(Request $request, Team $team)
    {
        // 1. Set Spatie team context immediately for authorization checks
        setPermissionsTeamId($team->id);
        $this->authorize('change member roles');

        // 2. Validate that 'updates' is an array of userId => role
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*' => 'required|string|in:owner,manager,member,guest',
        ]);

        $updates = $validated['updates'];

        // Guard: The owner cannot demote themselves directly; they must transfer ownership first
        $currentUserPivotRole = $team->users()->where('user_id', auth()->id())->value('role');
        if ($currentUserPivotRole === 'owner' && isset($updates[auth()->id()]) && $updates[auth()->id()] !== 'owner') {
            return redirect()->route('home', ['tab' => 'teams'])->withErrors([
                'role' => 'Il proprietario non può cambiare il proprio ruolo. Trasferisci prima la proprietà a un altro membro.'
            ]);
        }

        // Guard: Only the current team owner can transfer ownership
        if (in_array('owner', $updates)) {
            $isCurrentUserOwner = $team->users()
                ->where('user_id', auth()->id())
                ->where('role', 'owner')
                ->exists();

            if (!$isCurrentUserOwner) {
                return redirect()->route('home', ['tab' => 'teams'])->withErrors([
                    'role' => 'Solo il proprietario del team può trasferire la proprietà.'
                ]);
            }

            // Guard: Prevent setting more than one user as owner at a time
            $newOwnerCount = collect($updates)->filter(fn($r) => $r === 'owner')->count();
            if ($newOwnerCount > 1) {
                return redirect()->route('home', ['tab' => 'teams'])->withErrors([
                    'role' => 'Solo un proprietario è consentito per team.'
                ]);
            }
        }

        // 3. Compute the resulting role state after applying all updates
        $currentRoles = $team->users->pluck('pivot.role', 'id')->toArray();
        $resultingRoles = array_merge($currentRoles, $updates);

        // Account for the auto-demotion of the current owner when ownership is transferred
        $newOwnerIdFromUpdates = collect($updates)->search(fn($r) => $r === 'owner');
        if ($newOwnerIdFromUpdates !== false) {
            $currentOwner = $team->users->first(fn($u) => $u->pivot->role === 'owner');
            if ($currentOwner && (int)$newOwnerIdFromUpdates !== $currentOwner->id && !isset($updates[$currentOwner->id])) {
                $resultingRoles[$currentOwner->id] = 'member';
            }
        }

        // 4. Ensure the resulting state has at least one member with admin privileges
        $willHaveAdmin = collect($resultingRoles)
            ->filter(fn($role) => in_array($role, ['owner', 'manager']))
            ->isNotEmpty();

        if (!$willHaveAdmin) {
            return redirect()->route('home', ['tab' => 'teams'])->withErrors([
                'role' => 'Il team deve avere almeno un membro con permessi di gestione.'
            ]);
        }

        // 5. Execute updates in a Database Transaction & update roles
        \DB::transaction(function () use ($updates, $team) {
            // Handle ownership transfer: auto-demote the current owner when a new owner is being set
            $newOwnerIdFromUpdates = collect($updates)->search(fn($r) => $r === 'owner');
            if ($newOwnerIdFromUpdates !== false) {
                $currentOwner = $team->users->first(fn($u) => $u->pivot->role === 'owner');
                if ($currentOwner && (int)$newOwnerIdFromUpdates !== $currentOwner->id && !isset($updates[$currentOwner->id])) {
                    setPermissionsTeamId($team->id);
                    $team->users()->updateExistingPivot($currentOwner->id, ['role' => 'member']);
                    $currentOwner->syncRoles(['member']);
                    if (auth()->id() == $currentOwner->id) {
                        $currentOwner->forgetCachedPermissions();
                    }
                }
            }

            foreach ($updates as $userId => $roleName) {
                $user = \App\Models\User::find($userId);

                if ($user && $team->users->contains($user)) {

                    $team->users()->updateExistingPivot($userId, ['role' => $roleName]);

                    $user->syncRoles([$roleName]);

                    if (auth()->id() == $userId) {
                        $user->forgetCachedPermissions();
                    }
                }
            }
        });

        // 6. Clear global Spatie cache to ensure immediate effect
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('home', ['tab' => 'teams'])
            ->with('status', 'Roles updated successfully!');
    }

    /**
     * Cancel (revoke) a pending team invitation.
     *
     * Deletes the invitation record so the token can no longer be used.
     * Requires the 'invite members' permission on the invitation's team.
     */
    public function cancelInvitation(Invitation $invitation): \Illuminate\Http\RedirectResponse
    {
        setPermissionsTeamId($invitation->team_id);

        if (auth()->check()) {
            auth()->user()->unsetRelation('roles');
            auth()->user()->unsetRelation('permissions');
        }

        $this->authorize('invite members');

        $invitation->delete();

        return back()->with('status', 'Invito annullato con successo!');
    }
}
