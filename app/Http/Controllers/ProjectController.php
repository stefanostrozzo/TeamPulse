<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Stores a newly created project
     * @param Request $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        // 1. Ensure we are checking permissions against the correct team context
        setPermissionsTeamId($request->team_id);

        // 2. Authorize the user
        $this->authorize('create projects');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'progress' => 'integer|min:0|max:100',
            'team_id' => 'required|exists:teams,id',
        ]);

        //Use transaction for safety
        $project = DB::transaction(function () use ($validated) {

            $project = Project::create($validated);

            $teamMemberIds = Team::find($validated['team_id'])
                ->users()
                ->pluck('users.id')
                ->toArray();

            $project->members()->attach($teamMemberIds);

            return $project;
        });

        return redirect()->route('home', ['tab' => 'projects'])
            ->with('status', 'Project successfully created!');
    }

    /**
     * Updates the designed project
     * @param Request $request
     * @param Project $project
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function update(Request $request, Project $project)
    {
        setPermissionsTeamId($project->team_id);

        // Calls the update method on policy
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|string',
            'priority'    => 'required|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'progress'    => 'integer|min:0|max:100',
        ]);

        // Sync members to project
        DB::transaction(function () use ($project, $validated) {

            $project->update($validated);
            $teamMemberIds = $project->team->users()->pluck('users.id')->toArray();
            $project->members()->sync($teamMemberIds);
        });

        return redirect()->route('home', ['tab' => 'projects'])
            ->with('status', 'Project updated!');
    }

    /**
     * Eliminates the designed project
     * @param $id
     * @param Request $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function destroy($id, Request $request)
    {
        $project = Project::findOrFail($id);
        $activeTab = $request->get('tab', 'projects');
        // Ensure the deletion is authorized within the correct team context
        setPermissionsTeamId($project->team_id);
        $this->authorize('delete projects');

        //Use transaction for safety
        $projectToDestroy = DB::transaction(function () use ($project){

            $teamMemberIds = Team::find($project->team_id)
                ->users()
                ->pluck('users.id')
                ->toArray();

            $project->members()->detach($teamMemberIds);

            $project->delete();
        });

        return redirect()->route('home', ['tab' => $activeTab])
            ->with('status', 'Project successfully deleted!');
    }

}
