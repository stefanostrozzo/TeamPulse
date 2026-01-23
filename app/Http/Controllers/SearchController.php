<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function global(Request $request)
    {
        $query = $request->get('q');
        $user = Auth::user();
        $teamId = $user->current_team_id;

        if (!$query) {
            return response()->json([
                'projects' => [],
                'tasks' => [],
                'teams' => [],
                'members' => []
            ]);
        }

        $projects = Project::where('team_id', $teamId)
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'team_id']);

        $tasks = Task::whereHas('project', function($q) use ($teamId) {
            $q->where('team_id', $teamId);
        })
            ->where('title', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'title', 'project_id']);

        $teams = $user->teams()
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['teams.id', 'name']);

        $members = User::whereHas('teams', function($q) use ($teamId) {
            $q->where('teams.id', $teamId);
        })
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['users.id', 'name', 'email']);

        return response()->json([
            'projects' => $projects,
            'tasks'    => $tasks,
            'teams'    => $teams,
            'members'  => $members,
        ]);
    }
}
