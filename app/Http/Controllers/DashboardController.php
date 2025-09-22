<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'home');
        
        $data = [
            'activeTab' => $activeTab,
            'mustVerifyEmail' => $request->user()->mustVerifyEmail(),
            'status' => session('status'),
        ];
        
        // Se la tab attiva è progetti, carica i dati dei progetti
        if ($activeTab === 'progetti') {
            $query = Project::with(['client', 'tasks', 'members'])
                ->when($request->search, function ($q, $search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                })
                ->when($request->status && $request->status !== 'all', function ($q, $status) {
                    $q->where('status', $status);
                });

            $projects = $query->paginate(12);

            $data['projects'] = $projects;
            $data['filters'] = $request->only(['search', 'status', 'priority']);
            $data['stats'] = [
                'total' => Project::count(),
                'active' => Project::where('status', 'active')->count(),
                'completed' => Project::where('status', 'completed')->count(),
                'overdue' => Project::where('status', 'active')
                    ->where('end_date', '<', now())
                    ->count(),
            ];
        }
        
        return Inertia::render('Dashboard/Ind', $data);
    }
}