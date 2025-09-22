<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['client', 'tasks', 'members'])
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status && $request->status !== 'all', function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->priority && $request->priority !== 'all', function ($q, $priority) {
                $q->where('priority', $priority);
            });

        // Ordinamento
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $projects = $query->paginate(12);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status', 'priority', 'sort_field', 'sort_direction']),
            'stats' => [
                'total' => Project::count(),
                'active' => Project::active()->count(),
                'completed' => Project::completed()->count(),
                'overdue' => Project::active()->where('end_date', '<', now())->count(),
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create', [
            'clients' => Customer::all(),
            'users' => User::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'status' => 'required|in:planning,active,paused,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'color' => 'nullable|string',
            'tags' => 'nullable|array',
            'members' => 'nullable|array'
        ]);

        $project = Project::create($validated);

        if ($request->has('members')) {
            $project->members()->sync($request->members);
        }

        return redirect()->route('progetti.index')->with('success', 'Progetto creato con successo!');
    }

    public function show(Project $project)
    {
        return Inertia::render('Projects/Show', [
            'project' => $project->load(['client', 'tasks', 'members'])
        ]);
    }
}