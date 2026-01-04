<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the specific project.
     */
    public function view(User $user, Project $project): bool
    {
        setPermissionsTeamId($project->team_id);

        if ($user->can('view all projects')) {
            return true;
        }

        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return $user->can('create projects');
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        setPermissionsTeamId($project->team_id);

        //Reload Spatie permissions for the team
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return $user->can('edit projects');
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        setPermissionsTeamId($project->team_id);
        return $user->can('delete projects');
    }
}
