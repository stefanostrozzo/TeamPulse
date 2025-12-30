<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Customer;

class ProjectController extends Controller
{
    /**
     * @param Request $request
     */
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'progress' => 'integer|min:0|max:100',
        ]);

        Project::create($validated);
    }


    /**
     * @param $id
     * @return \App\Models\Project
     */
    public function getElement($id){
        return Project::findOrFail($id);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:customers,id',
            'status' => 'required|string',
            'priority' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'progress' => 'integer|min:0|max:100',
        ]);

        $project->update($validated);

        return redirect()->route('home', ['tab' => 'projects'])
            ->with('status', 'Project updated!');
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id, Request $request){

        $project = Project::findOrFail($id);
        $project->delete();

        $activeTab = $request->get('tab', 'projects');
        return redirect()->route('home', ['tab' => $activeTab])
            ->with('status', 'Project successfully deleted!');
    }

}
