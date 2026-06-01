<?php
namespace App\Http\Controllers;

use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            TaskType::where('team_id', $request->user()->current_team_id)
                ->orderBy('name')->get(['id', 'name', 'color'])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        setPermissionsTeamId($user->current_team_id);
        abort_if(!$user->hasAnyRole(['owner', 'manager']), 403, 'Solo i manager possono creare tipologie.');

        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);
        $teamId = $user->current_team_id;
        abort_if(TaskType::where('team_id', $teamId)->where('name', $validated['name'])->exists(),
            422, 'Nome già esistente per questo team.');

        return response()->json(TaskType::create([
            'team_id' => $teamId, 'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6366f1', 'created_by' => $user->id,
        ]), 201);
    }

    public function update(Request $request, TaskType $taskType): JsonResponse
    {
        $user = $request->user();
        setPermissionsTeamId($user->current_team_id);
        abort_if(!$user->hasAnyRole(['owner', 'manager']), 403);
        abort_if($taskType->team_id !== $user->current_team_id, 403);
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);
        $taskType->update($validated);
        return response()->json($taskType);
    }

    public function destroy(Request $request, TaskType $taskType): JsonResponse
    {
        $user = $request->user();
        setPermissionsTeamId($user->current_team_id);
        abort_if(!$user->hasAnyRole(['owner', 'manager']), 403);
        abort_if($taskType->team_id !== $user->current_team_id, 403);
        $taskType->delete();
        return response()->json(['ok' => true]);
    }
}
