<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created project in storage.
     * * @param Request $request
     * @return RedirectResponse
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

        Project::create($validated);

        return redirect()->route('home', ['tab' => 'projects'])
            ->with('status', 'Project successfully created!');
    }


    /**
     * Retrieve a specific project's data.
     * * @param int $id
     * @return Project
     */
    public function getElement($id)
    {
        $project = Project::findOrFail($id);

        // Scope the permission check to the project's owning team
        setPermissionsTeamId($project->team_id);
        $this->authorize('view all projects');

        return $project;
    }

    /**
     * Update the specified project in storage.
     * * @param Request $request
     * @param Project $project
     * @return RedirectResponse
     */
    public function update(Request $request, Project $project)
    {
        // Set the Spatie permission context to the project's team_id
        setPermissionsTeamId($project->team_id);

        // Authorize the edit permission
        $this->authorize('edit projects');

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|string',
            'priority'    => 'required|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'progress'    => 'integer|min:0|max:100',
        ]);

        $project->update($validated);

        return redirect()->route('home', ['tab' => 'projects'])
            ->with('status', 'Project updated!');
    }

    /**
     * Remove the specified project from storage.
     * * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy($id, Request $request)
    {
        $project = Project::findOrFail($id);

        // Ensure the deletion is authorized within the correct team context
        setPermissionsTeamId($project->team_id);
        $this->authorize('delete projects');

        $project->delete();

        $activeTab = $request->get('tab', 'projects');
        return redirect()->route('home', ['tab' => $activeTab])
            ->with('status', 'Project successfully deleted!');
    }

}
