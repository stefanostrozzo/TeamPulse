<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;

class TimeTrackingService
{
    /**
     * Start a new timer for $user on $task.
     * If the user has an active timer on any task, it is stopped first.
     */
    public function startTimer(Task $task, User $user): TimeEntry
    {
        // Stop any running timer before starting a new one
        $this->stopTimer($user);

        return TimeEntry::create([
            'task_id'      => $task->id,
            'user_id'      => $user->id,
            'logged_by_id' => $user->id,
            'started_at'   => now(),
        ]);
    }

    /**
     * Stop the active timer for $user.
     * Computes duration_seconds and updates task.time_spent.
     * Returns null if no active timer exists.
     */
    public function stopTimer(User $user): ?TimeEntry
    {
        $entry = $this->getActiveTimer($user);

        if (! $entry) {
            return null;
        }

        $endedAt  = now();
        $duration = abs($endedAt->diffInSeconds($entry->started_at, false));

        $entry->update([
            'ended_at'         => $endedAt,
            'duration_seconds' => (int) $duration,
        ]);

        // Keep tasks.time_spent in sync (stored as fractional hours)
        $entry->task->increment('time_spent', round($duration / 3600, 4));

        return $entry->fresh()->load(['user:id,name', 'task.project']);
    }

    /**
     * Log a completed time block manually.
     * $loggedBy may differ from $forUser when a manager logs on behalf of someone.
     */
    public function logManual(Task $task, User $loggedBy, User $forUser, array $data): TimeEntry
    {
        $startedAt = Carbon::parse($data['started_at']);
        $endedAt   = Carbon::parse($data['ended_at']);
        $duration  = abs($endedAt->diffInSeconds($startedAt, false));

        $entry = TimeEntry::create([
            'task_id'          => $task->id,
            'user_id'          => $forUser->id,
            'logged_by_id'     => $loggedBy->id,
            'started_at'       => $startedAt,
            'ended_at'         => $endedAt,
            'duration_seconds' => (int) $duration,
            'description'      => $data['description'] ?? null,
        ]);

        $task->increment('time_spent', round($duration / 3600, 4));

        return $entry->load(['user:id,name', 'task.project']);
    }

    /**
     * Update an existing completed entry.
     * Recalculates duration and adjusts task.time_spent by the delta.
     */
    public function updateEntry(TimeEntry $entry, array $data): TimeEntry
    {
        $oldDuration = $entry->duration_seconds ?? 0;

        $startedAt   = Carbon::parse($data['started_at']);
        $endedAt     = Carbon::parse($data['ended_at']);
        $newDuration = abs($endedAt->diffInSeconds($startedAt, false));

        $entry->update([
            'started_at'       => $startedAt,
            'ended_at'         => $endedAt,
            'duration_seconds' => (int) $newDuration,
            'description'      => $data['description'] ?? $entry->description,
        ]);

        $delta = ($newDuration - $oldDuration) / 3600;
        $entry->task->increment('time_spent', round($delta, 4));

        return $entry->fresh()->load(['user:id,name', 'task.project']);
    }

    /**
     * Delete an entry and subtract its duration from task.time_spent.
     */
    public function deleteEntry(TimeEntry $entry): void
    {
        $duration = $entry->duration_seconds ?? 0;
        // Only subtract if entry was completed; active timer has no duration yet
        if ($duration > 0) {
            $entry->task->decrement('time_spent', round($duration / 3600, 4));
        }
        $entry->delete();
    }

    /**
     * Return the user's currently active (running) timer, or null.
     */
    public function getActiveTimer(User $user): ?TimeEntry
    {
        return TimeEntry::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->with('task.project')
            ->first();
    }

    /**
     * Personal report: all completed entries for $user in $period, scoped to $teamId.
     * Returns a shape ready for the frontend chart + table.
     */
    public function getPersonalReport(User $user, string $period, ?int $teamId): array
    {
        [$start, $end] = $this->periodBounds($period);

        $entries = TimeEntry::where('user_id', $user->id)
            ->whereNotNull('ended_at')
            ->whereBetween('started_at', [$start, $end])
            ->when($teamId, fn ($q) => $q->whereHas('task', fn ($tq) => $tq->where('team_id', $teamId)))
            ->with(['task:id,title,project_id', 'task.project:id,name'])
            ->orderBy('started_at', 'desc')
            ->get();

        return [
            'entries'      => $entries,
            'total_seconds' => $entries->sum('duration_seconds'),
            'by_project'   => $entries
                ->groupBy(fn ($e) => $e->task->project->name ?? 'Senza Progetto')
                ->map(fn ($g) => $g->sum('duration_seconds')),
            'by_day'       => $entries
                ->groupBy(fn ($e) => $e->started_at->toDateString())
                ->map(fn ($g) => $g->sum('duration_seconds')),
        ];
    }

    /**
     * Team report: all completed entries for all users in $teamId in $period.
     * Only callable by managers â€” controller enforces policy.
     */
    public function getTeamReport(int $teamId, string $period): array
    {
        [$start, $end] = $this->periodBounds($period);

        $entries = TimeEntry::whereNotNull('ended_at')
            ->whereHas('task', fn ($q) => $q->where('team_id', $teamId))
            ->whereBetween('started_at', [$start, $end])
            ->with(['user:id,name', 'task:id,title,project_id', 'task.project:id,name'])
            ->orderBy('started_at', 'desc')
            ->get();

        $byUser = $entries->groupBy('user_id')->map(function ($group) {
            return [
                'user'          => $group->first()->user,
                'total_seconds' => $group->sum('duration_seconds'),
                'by_project'    => $group
                    ->groupBy(fn ($e) => $e->task->project->name ?? 'Senza Progetto')
                    ->map(fn ($g) => $g->sum('duration_seconds')),
            ];
        })->values();

        return [
            'entries'       => $entries,
            'total_seconds' => $entries->sum('duration_seconds'),
            'by_user'       => $byUser,
        ];
    }

    /**
     * Return [Carbon $start, Carbon $end] for the given period string.
     */
    private function periodBounds(string $period): array
    {
        return match ($period) {
            'week'       => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week'  => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default      => [now()->startOfMonth(), now()->endOfMonth()], // 'month'
        };
    }
}
