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
     * Handle the main dashboard view and tab switching.
     */
    public function index(Request $request): Response
    {
        $activeTab = $request->get('tab', 'home');

        return Inertia::render('Home', [
            // Current active tab state
            'activeTab' => $activeTab,

            // Basic session and auth status
            'mustVerifyEmail' => $request->user()->hasVerifiedEmail(),
            'status' => session('status'),

            // Projects data: Loaded lazily only when the 'projects' tab is active
            'projects' => ($activeTab === 'projects')
                ? Project::with(['tasks', 'members'])->get()
                : [],

            'stats' => ($activeTab === 'projects') ? [
                'total'     => Project::count(),
                'active'    => Project::where('status', 'active')->count(),
                'completed' => Project::where('status', 'completed')->count(),
                'overdue'   => Project::where('status', 'active')
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
