<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\Team;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\TimeTrackingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TimeTrackingTest extends TestCase
{
    use DatabaseTransactions;

    // â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function makeTaskWithTeam(): array
    {
        $team = Team::factory()->create();
        $user = User::factory()->create(['current_team_id' => $team->id]);
        $team->users()->attach($user);

        $project = \App\Models\Project::factory()->create(['team_id' => $team->id]);

        $task = Task::factory()->create([
            'team_id'    => $team->id,
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        return [$task, $user, $team];
    }

    // â”€â”€ Timer: start â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_user_can_start_timer(): void
    {
        [$task, $user] = $this->makeTaskWithTeam();

        $response = $this->actingAs($user)->postJson(route('time.start'), [
            'task_id' => $task->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.task_id', $task->id)
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('time_entries', [
            'task_id'  => $task->id,
            'user_id'  => $user->id,
            'ended_at' => null,
        ]);
    }

    public function test_starting_new_timer_stops_previous_active_timer(): void
    {
        [$task1, $user] = $this->makeTaskWithTeam();
        $team    = Team::find($task1->team_id);
        $project = \App\Models\Project::factory()->create(['team_id' => $team->id]);
        $task2   = Task::factory()->create([
            'team_id'    => $team->id,
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        // Start first timer
        $this->actingAs($user)->postJson(route('time.start'), ['task_id' => $task1->id]);

        // Start second timer â€” first must be stopped
        $this->actingAs($user)->postJson(route('time.start'), ['task_id' => $task2->id]);

        // task1 entry must now be completed
        $this->assertDatabaseMissing('time_entries', [
            'task_id'  => $task1->id,
            'user_id'  => $user->id,
            'ended_at' => null,
        ]);

        // task2 entry must be active
        $this->assertDatabaseHas('time_entries', [
            'task_id'  => $task2->id,
            'user_id'  => $user->id,
            'ended_at' => null,
        ]);
    }

    // â”€â”€ Timer: stop â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_user_can_stop_active_timer(): void
    {
        [$task, $user] = $this->makeTaskWithTeam();

        // Start timer
        $this->actingAs($user)->postJson(route('time.start'), ['task_id' => $task->id]);

        // Stop timer
        $response = $this->actingAs($user)->postJson(route('time.stop'));

        $response->assertStatus(200)
            ->assertJsonPath('data.task_id', $task->id);

        $entry = TimeEntry::where('task_id', $task->id)->where('user_id', $user->id)->first();
        $this->assertNotNull($entry->ended_at);
        $this->assertNotNull($entry->duration_seconds);
    }

    public function test_stop_returns_404_when_no_active_timer(): void
    {
        [, $user] = $this->makeTaskWithTeam();

        $response = $this->actingAs($user)->postJson(route('time.stop'));

        $response->assertStatus(404);
    }

    // â”€â”€ Manual log â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_user_can_log_manual_entry(): void
    {
        [$task, $user] = $this->makeTaskWithTeam();

        $response = $this->actingAs($user)->postJson(route('time.store', $task), [
            'started_at'  => '2026-05-25 09:00:00',
            'ended_at'    => '2026-05-25 11:30:00',
            'description' => 'Backend development',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('time_entries', [
            'task_id'          => $task->id,
            'user_id'          => $user->id,
            'duration_seconds' => 9000, // 2.5 hours Ã— 3600
            'description'      => 'Backend development',
        ]);

        // task.time_spent should increase
        $this->assertEquals(2.5, (float) $task->fresh()->time_spent);
    }

    public function test_manager_can_log_on_behalf_of_team_member(): void
    {
        [$task, $member, $team] = $this->makeTaskWithTeam();
        $manager = User::factory()->create(['current_team_id' => $team->id]);
        $team->users()->attach($manager);

        // Give manager the 'manager' role
        setPermissionsTeamId($team->id);
        $manager->assignRole('manager');

        $response = $this->actingAs($manager)->postJson(route('time.store', $task), [
            'started_at'  => '2026-05-25 08:00:00',
            'ended_at'    => '2026-05-25 10:00:00',
            'user_id'     => $member->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('time_entries', [
            'task_id'      => $task->id,
            'user_id'      => $member->id,
            'logged_by_id' => $manager->id,
        ]);
    }

    public function test_non_manager_cannot_log_on_behalf(): void
    {
        [$task, $member, $team] = $this->makeTaskWithTeam();
        $other = User::factory()->create(['current_team_id' => $team->id]);
        $team->users()->attach($other);

        $response = $this->actingAs($other)->postJson(route('time.store', $task), [
            'started_at' => '2026-05-25 08:00:00',
            'ended_at'   => '2026-05-25 10:00:00',
            'user_id'    => $member->id,
        ]);

        $response->assertStatus(403);
    }

    // â”€â”€ Update / Delete â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function test_user_can_update_own_entry(): void
    {
        [$task, $user] = $this->makeTaskWithTeam();

        $entry = TimeEntry::create([
            'task_id'          => $task->id,
            'user_id'          => $user->id,
            'logged_by_id'     => $user->id,
            'started_at'       => '2026-05-25 09:00:00',
            'ended_at'         => '2026-05-25 10:00:00',
            'duration_seconds' => 3600,
        ]);

        $response = $this->actingAs($user)->putJson(route('time.update', $entry), [
            'started_at' => '2026-05-25 09:00:00',
            'ended_at'   => '2026-05-25 11:00:00',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(7200, $entry->fresh()->duration_seconds);
    }

    public function test_user_cannot_update_another_users_entry(): void
    {
        [$task, $user, $team] = $this->makeTaskWithTeam();
        $other = User::factory()->create(['current_team_id' => $team->id]);

        $entry = TimeEntry::create([
            'task_id'          => $task->id,
            'user_id'          => $user->id,
            'logged_by_id'     => $user->id,
            'started_at'       => '2026-05-25 09:00:00',
            'ended_at'         => '2026-05-25 10:00:00',
            'duration_seconds' => 3600,
        ]);

        $response = $this->actingAs($other)->putJson(route('time.update', $entry), [
            'started_at' => '2026-05-25 09:00:00',
            'ended_at'   => '2026-05-25 11:00:00',
        ]);

        $response->assertStatus(403);
    }

    public function test_delete_entry_decrements_task_time_spent(): void
    {
        [$task, $user] = $this->makeTaskWithTeam();

        $task->time_spent = 2.0;
        $task->save();

        $entry = TimeEntry::create([
            'task_id'          => $task->id,
            'user_id'          => $user->id,
            'logged_by_id'     => $user->id,
            'started_at'       => '2026-05-25 09:00:00',
            'ended_at'         => '2026-05-25 10:00:00',
            'duration_seconds' => 3600,
        ]);

        $this->actingAs($user)->deleteJson(route('time.destroy', $entry));

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
        $this->assertEquals(1.0, (float) $task->fresh()->time_spent);
    }
}
