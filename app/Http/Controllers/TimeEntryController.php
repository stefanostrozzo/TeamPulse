<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Requests\UpdateTimeEntryRequest;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\TimeTrackingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private TimeTrackingService $service) {}

    /**
     * Return all completed time entries for a given task (excludes active timers).
     */
    public function index(Task $task): JsonResponse
    {
        $entries = $task->timeEntries()
            ->whereNotNull('ended_at')
            ->with(['user:id,name', 'loggedBy:id,name'])
            ->get();

        return response()->json([
            'entries'          => $entries,
            'time_spent'       => $task->time_spent,
            'estimated_hours'  => $task->estimated_hours,
        ]);
    }

    /**
     * Start a new timer for the authenticated user on the given task.
     * Any previously active timer is stopped automatically.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate(['task_id' => 'required|exists:tasks,id']);

        $task = Task::findOrFail($request->task_id);

        $entry = $this->service->startTimer($task, $request->user());

        return response()->json(['data' => $entry->load('task.project')], 201);
    }

    /**
     * Stop the authenticated user's active timer.
     */
    public function stop(Request $request): JsonResponse
    {
        $entry = $this->service->stopTimer($request->user());

        if (! $entry) {
            return response()->json(['message' => 'Nessun timer attivo'], 404);
        }

        return response()->json(['data' => $entry]);
    }

    /**
     * Manually log a completed time block on a task.
     */
    public function store(StoreTimeEntryRequest $request, Task $task): JsonResponse
    {
        $forUser = $request->user_id
            ? User::findOrFail($request->user_id)
            : $request->user();

        // Logging for another user requires manager role
        if ($forUser->id !== $request->user()->id) {
            $this->authorize('logOnBehalf', TimeEntry::class);
        }

        $entry = $this->service->logManual($task, $request->user(), $forUser, $request->validated());

        return response()->json(['data' => $entry], 201);
    }

    /**
     * Update an existing time entry.
     */
    public function update(UpdateTimeEntryRequest $request, TimeEntry $entry): JsonResponse
    {
        $this->authorize('update', $entry);

        $entry = $this->service->updateEntry($entry, $request->validated());

        return response()->json(['data' => $entry]);
    }

    /**
     * Delete a time entry.
     */
    public function destroy(TimeEntry $entry): JsonResponse
    {
        $this->authorize('delete', $entry);

        $this->service->deleteEntry($entry);

        return response()->json(null, 204);
    }

    /**
     * Return report data (personal or team) as JSON.
     * Used by TimeReportView.vue via fetch().
     */
    public function report(Request $request): JsonResponse
    {
        $user       = $request->user();
        $period     = $request->get('period', 'month');
        $projectId  = $request->get('project_id') ? (int) $request->get('project_id') : null;
        $startDate  = $request->get('start_date');
        $endDate    = $request->get('end_date');
        $teamId     = $user->current_team_id;

        if ($request->boolean('team')) {
            $this->authorize('viewTeamReport', TimeEntry::class);
            $data = $this->service->getTeamReport($teamId, $period, $projectId, $startDate, $endDate);
        } else {
            $data = $this->service->getPersonalReport($user, $period, $teamId, $projectId, $startDate, $endDate);
        }

        // Aggrega la lista di tutti i progetti del team per riempire il menu a tendina
        $data['projects'] = \App\Models\Project::where('team_id', $teamId)
            ->select('id', 'name')
            ->get();

        return response()->json($data);
    }

    /**
     * Renderizza un report di attività professionale ottimizzato per la stampa o PDF.
     */
    public function printReport(Request $request)
    {
        $user       = $request->user();
        $period     = $request->get('period', 'month');
        $projectId  = $request->get('project_id') ? (int) $request->get('project_id') : null;
        $startDate  = $request->get('start_date');
        $endDate    = $request->get('end_date');
        $teamId     = $user->current_team_id;
        $isTeam     = $request->boolean('team');

        if ($isTeam) {
            $this->authorize('viewTeamReport', TimeEntry::class);
            $data = $this->service->getTeamReport($teamId, $period, $projectId, $startDate, $endDate);
        } else {
            $data = $this->service->getPersonalReport($user, $period, $teamId, $projectId, $startDate, $endDate);
        }

        // Raggruppa i dati per giorno in ordine cronologico inverso
        $entriesGrouped = $data['entries']->groupBy(function ($entry) {
            return \Carbon\Carbon::parse($entry->started_at)->toDateString();
        })->sortKeysDesc();

        $selectedProject = $projectId ? \App\Models\Project::find($projectId) : null;

        return view('reports.print', [
            'entriesGrouped'  => $entriesGrouped,
            'totalSeconds'    => $data['total_seconds'],
            'isTeam'          => $isTeam,
            'user'            => $user,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'period'          => $period,
            'selectedProject' => $selectedProject,
            'byProject'       => $data['by_project'] ?? [],
        ]);
    }
}
