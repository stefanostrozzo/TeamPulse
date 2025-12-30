<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Customer;

class ProjectController extends Controller
{

    /**
     * @param Request $request
     * @return void
     *
     */
    public function store(Request $request){
        //TODO: Data validation + DB storage of info
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
     * @return void
     */
    public function update(Request $request, $id){
        //TODO: Based on the id edit the corrisponding record
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
