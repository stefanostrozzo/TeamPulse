<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimeEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Edit an entry: entry owner, whoever logged it, or a team manager.
     */
    public function update(User $user, TimeEntry $entry): bool
    {
        return $user->id === $entry->user_id
            || $user->id === $entry->logged_by_id
            || $user->hasAnyRole(['owner', 'manager']);
    }

    /**
     * Delete an entry: same rules as update.
     */
    public function delete(User $user, TimeEntry $entry): bool
    {
        return $user->id === $entry->user_id
            || $user->id === $entry->logged_by_id
            || $user->hasAnyRole(['owner', 'manager']);
    }

    /**
     * Log time on behalf of another team member.
     * Restricted to owners and managers.
     */
    public function logOnBehalf(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'manager']);
    }

    /**
     * View the full team report.
     * Restricted to owners and managers.
     */
    public function viewTeamReport(User $user): bool
    {
        return $user->hasAnyRole(['owner', 'manager']);
    }
}
