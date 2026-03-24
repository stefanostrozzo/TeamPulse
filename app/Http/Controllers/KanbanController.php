<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class KanbanController extends Controller
{
    private function checkAccess(&$project)
    {
        if (!$project->exists && request()->route('project')) {
            $projectId = request()->route('project') instanceof Project 
                            ? request()->route('project')->id 
                            : request()->route('project');
            $project = Project::findOrFail($projectId);
        }

        $user = auth()->user();
        if ($user->current_team_id !== $project->team_id) {
            abort(403, 'Unauthorized access to project kanban context.');
        }
    }

    public function index(Project $project)
    {
        $this->checkAccess($project);

        $columns = auth()->user()->kanbanColumns()
            ->where('project_id', $project->id)
            ->with(['kanbanTasks' => function ($query) {
                $query->orderBy('order');
            }, 'kanbanTasks.task.assignee', 'kanbanTasks.task.comments'])
            ->orderBy('order')
            ->get();

        return response()->json(['columns' => $columns]);
    }

    public function storeColumn(Request $request, Project $project)
    {
        $this->checkAccess($project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxOrder = auth()->user()->kanbanColumns()
            ->where('project_id', $project->id)
            ->max('order') ?? -1;

        $column = auth()->user()->kanbanColumns()->create([
            'project_id' => $project->id,
            'name' => $validated['name'],
            'order' => $maxOrder + 1,
        ]);

        return response()->json(['column' => $column]);
    }

    public function updateColumn(Request $request, Project $project, KanbanColumn $column)
    {
        $this->checkAccess($project);

        if ($column->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $column->update($validated);

        return response()->json(['column' => $column]);
    }

    public function destroyColumn(Project $project, KanbanColumn $column)
    {
        $this->checkAccess($project);

        if ($column->user_id !== auth()->id()) {
            abort(403);
        }

        $column->delete();

        return response()->json(['success' => true]);
    }

    public function reorderColumns(Request $request, Project $project)
    {
        $this->checkAccess($project);

        $validated = $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:kanban_columns,id',
            'columns.*.order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['columns'] as $colData) {
                KanbanColumn::where('id', $colData['id'])
                    ->where('user_id', auth()->id())
                    ->update(['order' => $colData['order']]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function addTask(Request $request, Project $project)
    {
        $this->checkAccess($project);

        $validated = $request->validate([
            'kanban_column_id' => 'required|exists:kanban_columns,id',
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        // Ensure the column belongs to user
        $column = KanbanColumn::where('id', $validated['kanban_column_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $maxOrder = KanbanTask::where('kanban_column_id', $column->id)
            ->max('order') ?? -1;

        $addedTasks = DB::transaction(function () use ($validated, $project, $column, &$maxOrder) {
            $inserted = [];

            foreach ($validated['task_ids'] as $taskId) {
                // Ensure task belongs to project
                $task = $project->tasks()->findOrFail($taskId);

                // Check if task is already anywhere in the user's board
                $existingKanbanTask = KanbanTask::where('task_id', $task->id)
                    ->whereHas('kanbanColumn', function ($q) use ($project) {
                        $q->where('user_id', auth()->id())
                          ->where('project_id', $project->id);
                    })->first();

                if (!$existingKanbanTask) {
                    $maxOrder++;
                    $kanbanTask = KanbanTask::create([
                        'kanban_column_id' => $column->id,
                        'task_id' => $task->id,
                        'order' => $maxOrder,
                    ]);
                    $inserted[] = $kanbanTask;
                }
            }
            
            return $inserted;
        });

        // Load relationships and return
        foreach ($addedTasks as $kanbanTask) {
            $kanbanTask->load(['task.assignee', 'task.comments']);
        }

        return response()->json(['kanbanTasks' => $addedTasks]);
    }

    public function reorderTasks(Request $request, Project $project)
    {
        $this->checkAccess($project);

        // Expect an array of objects representing columns and their tasks in order.
        // columns: [ { id: column_id, tasks: [ kanban_task_id_1, kanban_task_id_2, ... ] } ]
        $validated = $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:kanban_columns,id',
            'columns.*.tasks' => 'present|array',
            'columns.*.tasks.*' => 'exists:kanban_tasks,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['columns'] as $colData) {
                $colId = $colData['id'];
                
                // Verify column belongs to user
                $column = KanbanColumn::where('id', $colId)
                    ->where('user_id', auth()->id())
                    ->first();
                    
                if (!$column) continue;

                foreach ($colData['tasks'] as $index => $kanbanTaskId) {
                    KanbanTask::where('id', $kanbanTaskId)
                        ->update([
                            'kanban_column_id' => $colId,
                            'order' => $index,
                        ]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function removeTask(Project $project, KanbanTask $kanbanTask)
    {
        $this->checkAccess($project);

        // Verify it belongs to the user's board
        $column = $kanbanTask->kanbanColumn;
        if ($column->user_id !== auth()->id() || $column->project_id !== $project->id) {
            abort(403);
        }

        $kanbanTask->delete();

        return response()->json(['success' => true]);
    }
}
