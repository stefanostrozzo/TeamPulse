<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Creates the task
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Ensure we are checking permissions against the correct team context
        setPermissionsTeamId($request->team_id);

        // 2. Authorize the user
        $this->authorize('create tasks');

        // 3. Data validation
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'team_id' => 'required|exists:teams,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in-progress,done,blocked',
            'priority' => 'required|in:low,medium,high',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'progress' => 'integer|min:0|max:100',
        ]);

        $validated['created_by'] = auth()->id();

        // 4. Create task
        Task::create($validated);

        return back()->with('status', 'Task creata correttamente!');
    }
}
