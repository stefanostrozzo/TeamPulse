<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{

    /**
     * Handle the incoming request to populate the main dashboard view.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $activeTab = $request->get('tab', 'dashboard');
        $currentTeamId = $user->current_team_id;

        return Inertia::render('Home', [
            'activeTab' => $activeTab,
            'currentTeamId' => $currentTeamId,
            'teamsCount' => $user->teams()->count(),

            /*
            |--------------------------------------------------------------------------
            | Team Context & Permissions
            |--------------------------------------------------------------------------
            | Map through user teams to establish the Spatie permission context
            | for each team, providing granular UI authorization flags.
            */
            'userTeams' => $user->teams()
                ->with(['users' => function($query) {
                    $query->select('users.id', 'users.name', 'users.email');
                }])
                ->withCount('users')
                ->get()
                ->map(function ($team) use ($user) {
                    setPermissionsTeamId($team->id);
                    $user->unsetRelation('roles');
                    $user->unsetRelation('permissions');

                    return array_merge($team->toArray(), [
                        'can_delete' => $user->can('delete team') ?? false,
                        'can_manage_team' => $user->can('manage team settings') ?? true,
                        'can_edit_roles' => $user->can('change member roles') ?? false,
                    ]);
                }),

            /*
            |--------------------------------------------------------------------------
            | Home Tab Data (KPIs & Previews)
            |--------------------------------------------------------------------------
            | Data specifically for the 'dashboard' tab, including ApexCharts metrics
            | and project-specific task previews assigned to the current user.
            */
            'homeData' => ($activeTab === 'dashboard' && $currentTeamId) ? [
                // KPI: Personal Task Status (Pie Chart)
                'myTasksStats' => [
                    'labels' => ['Completed', 'In Progress'],
                    'series' => [
                        Task::where('assignee_id', $user->id)->where('status', 'completed')->count(),
                        Task::where('assignee_id', $user->id)->where('status', '!=', 'completed')->count(),
                    ]
                ],

                // KPI: Open Tasks by Priority (Bar Chart)
                'tasksByPriority' => [
                    'categories' => ['High', 'Medium', 'Low'],
                    'series' => [
                        Task::where('assignee_id', $user->id)->where('status', '!=', 'completed')->where('priority', 'high')->count(),
                        Task::where('assignee_id', $user->id)->where('status', '!=', 'completed')->where('priority', 'medium')->count(),
                        Task::where('assignee_id', $user->id)->where('status', '!=', 'completed')->where('priority', 'low')->count(),
                    ]
                ],

                // Project Previews: Projects in the current team where the user has tasks
                'projectPreviews' => Project::where('team_id', $currentTeamId)
                    ->with(['tasks' => function($q) use ($user) {
                        $q->where('assignee_id', $user->id)->limit(3);
                    }])
                    ->whereHas('tasks', function($q) use ($user) {
                        $q->where('assignee_id', $user->id);
                    })
                    ->get(),

                // Management shortcut: Check if user has administrative rights in the team
                'isManager' => $user->hasAnyRole(['owner', 'manager']),
            ] : null,

            /*
            |--------------------------------------------------------------------------
            | Projects Tab Data
            |--------------------------------------------------------------------------
            | Lazy-loaded project data and general team statistics.
            */
            'projects' => ($activeTab === 'projects' && $currentTeamId)
                ? Project::where('team_id', $currentTeamId)
                    ->with(['tasks.comments.user', 'members', 'tasks.assignee'])
                    ->get()
                    ->map(function ($project) use ($user) {
                        setPermissionsTeamId($project->team_id);
                        $project->tasks->each(function ($task) use ($user) {
                            $task->comments->each(function ($comment) use ($user) {
                                $comment->can_edit = $user->id === $comment->created_by;
                                $comment->can_delete = ($user->id === $comment->created_by) || $user->can('delete tasks');
                            });
                        });
                        return $project;
                    })
                : [],

            'stats' => ($activeTab === 'projects' && $currentTeamId) ? [
                'total'     => Project::where('team_id', $currentTeamId)->count() ?? 0,
                'active'    => Project::where('team_id', $currentTeamId)->where('status', 'active')->count() ?? 0,
                'completed' => Project::where('team_id', $currentTeamId)->where('status', 'completed')->count() ?? 0,
                'overdue'   => Project::where('team_id', $currentTeamId)
                        ->where('status', 'active')
                        ->where('end_date', '<', now())
                        ->count() ?? 0,
            ] : null,

            'mustVerifyEmail' => $user->hasVerifiedEmail(),
            'status' => session('status'),
            'filters' => $request->only(['search', 'status', 'priority']),
        ]);
    }
}
