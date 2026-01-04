<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $activeTab = $request->get('tab', 'home');
        $currentTeamId = $user->current_team_id;

        return Inertia::render('Home', [
            // Current active tab state
            'activeTab' => $activeTab,
            'currentTeamId' => $currentTeamId,

            'teamsCount' => $user->teams()->count(),
            'userTeams' => $user->teams()
                ->with(['users' => function($query) {
                    $query->select('users.id', 'users.name', 'users.email');
                }])
                ->withCount('users')
                ->get()
                ->map(function ($team) use ($user) {
                    setPermissionsTeamId($team->id);

                    return array_merge($team->toArray(), [
                        'can_delete' => $user->can('delete team'),
                        'can_edit_roles' => $user->can('change member roles'),
                    ]);
                }),

            // Basic session and auth status
            'mustVerifyEmail' => $request->user()->hasVerifiedEmail(),
            'status' => session('status'),

            // Projects data: Loaded lazily only when the 'projects' tab is active
            'projects' => ($activeTab === 'projects' && $currentTeamId)
                ? Project::where('team_id', $currentTeamId)->with(['tasks', 'members'])->get()
                : [],

            'stats' => ($activeTab === 'projects' && $currentTeamId) ? [
                'total'     => Project::where('team_id', $currentTeamId)->count(),
                'active'    => Project::where('team_id', $currentTeamId)->where('status', 'active')->count(),
                'completed' => Project::where('team_id', $currentTeamId)->where('status', 'completed')->count(),
                'overdue'   => Project::where('team_id', $currentTeamId)
                    ->where('status', 'active')
                    ->where('end_date', '<', now())
                    ->count(),
            ] : null,

            // Management Data: Replaces the manual fetch() calls for Permissions tab
            'managementData' => Inertia::optional(function () use ($activeTab, $request) {
                // Security check: Only load if tab is active and user has administrative rights
                if ($activeTab === 'permissions' && $request->user()->can('manage users')) {
                    return [
                        'users' => User::with(['roles', 'permissions'])->get(),
                        'roles' => Role::all(),
                        'permissions' => Permission::all(),
                    ];
                }
                return null;
            }),

            // Filters persistence for the UI
            'filters' => $request->only(['search', 'status', 'priority']),
        ]);
    }
}
