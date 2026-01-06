<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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
            'task_parent_id' => 'nullable|exists:tasks,id',
        ]);

        $validated['created_by'] = auth()->id();

        // 4. Create task
        Task::create($validated);

        return back()->with('status', 'Task creata correttamente!');
    }

    /**
     * Update the specified task in storage.
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     *
     */
    public function update(Request $request, Task $task)
    {
        // 1. Ensure the correct team context for Spatie permissions
        setPermissionsTeamId($task->team_id);

        // 2. Authorize the action using TaskPolicy
        $this->authorize('update', $task);

        // 3. Validate the incoming request
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:todo,in-progress,done,blocked',
            'priority'    => 'required|in:low,medium,high',
            'type'        => 'required|string',
            'assignee_id' => 'nullable|exists:users,id',
            'start_date'  => 'nullable|date',
            'due_date'    => 'nullable|date',
            'progress'    => 'integer|min:0|max:100',
            'task_parent_id' => 'nullable|exists:tasks,id',
        ]);

        // 4. Execute update within a transaction to ensure data integrity
        DB::transaction(function () use ($task, $validated) {
            $task->update($validated);
        });

        $task->load(['project.tasks.assignee']);
        // 5. Redirect back to the project view with the 'elenco' tab active
        return back()->with('status', 'Attività aggiornata con successo!');
    }

    /**
     * Remove the specified task from storage.
     *
     * @param Task $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Task $task)
    {
        // 1. Ensure the correct team context for Spatie permissions
        setPermissionsTeamId($task->team_id);

        // 2. Authorize the action using TaskPolicy
        // This will call the 'delete' method in your TaskPolicy
        $this->authorize('delete', $task);

        // 3. Delete the task
        $task->delete();

        // 4. Redirect back maintaining the SPA state
        return back()->with('status', 'Attività eliminata con successo!');
    }
}
