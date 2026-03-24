<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KanbanTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $team;
    private $project;
    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::create(['name' => 'Test Team']);
        $this->user->teams()->attach($this->team->id, ['role' => 'owner']);
        $this->user->update(['current_team_id' => $this->team->id]);

        $this->project = Project::create([
            'team_id' => $this->team->id,
            'name' => 'Test Project',
            'status' => 'active',
            'priority' => 'low'
        ]);

        $this->user->refresh();
        $this->project->refresh();

        $this->task = collect([
            Task::create([
                'project_id' => $this->project->id,
                'team_id' => $this->team->id,
                'title' => 'Test task',
                'status' => 'todo',
                'priority' => 'high',
                'created_by' => $this->user->id
            ])
        ])->first();

        $this->actingAs($this->user);
    }

    public function test_kanban_columns_can_be_fetched()
    {
        $this->withoutExceptionHandling();

        KanbanColumn::create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'name' => 'To Do',
            'order' => 0
        ]);

        $response = $this->getJson("/projects/{$this->project->id}/kanban/columns");

        $response->assertStatus(200);
        $response->assertJsonStructure(['columns' => [['id', 'name', 'kanban_tasks']]]);
        $this->assertCount(1, $response->json('columns'));
    }

    public function test_kanban_column_can_be_created()
    {
        $response = $this->postJson("/projects/{$this->project->id}/kanban/columns", [
            'name' => 'In Progress'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('kanban_columns', [
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'name' => 'In Progress',
            'order' => 0
        ]);
    }

    public function test_kanban_column_can_be_renamed()
    {
        $column = KanbanColumn::create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'name' => 'To Do',
            'order' => 0
        ]);

        $response = $this->putJson("/projects/{$this->project->id}/kanban/columns/{$column->id}", [
            'name' => 'Doing'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Doing', $column->fresh()->name);
    }

    public function test_task_can_be_added_to_kanban_column()
    {
        $column = KanbanColumn::create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'name' => 'To Do',
            'order' => 0
        ]);

        $response = $this->postJson("/projects/{$this->project->id}/kanban/tasks", [
            'kanban_column_id' => $column->id,
            'task_ids' => [$this->task->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('kanban_tasks', [
            'kanban_column_id' => $column->id,
            'task_id' => $this->task->id,
            'order' => 0
        ]);
    }

    public function test_tasks_can_be_reordered()
    {
        $column1 = KanbanColumn::create(['user_id' => $this->user->id, 'project_id' => $this->project->id, 'name' => 'Col 1', 'order' => 0]);
        $column2 = KanbanColumn::create(['user_id' => $this->user->id, 'project_id' => $this->project->id, 'name' => 'Col 2', 'order' => 1]);

        $kanbanTask = KanbanTask::create([
            'kanban_column_id' => $column1->id,
            'task_id' => $this->task->id,
            'order' => 0
        ]);

        $response = $this->putJson("/projects/{$this->project->id}/kanban/tasks-reorder", [
            'columns' => [
                [
                    'id' => $column2->id,
                    'tasks' => [$kanbanTask->id]
                ]
            ]
        ]);

        $response->assertStatus(200);
        $this->assertEquals($column2->id, $kanbanTask->fresh()->kanban_column_id);
    }

    public function test_multiple_tasks_can_be_added_to_kanban_column()
    {
        $column = KanbanColumn::create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'name' => 'To Do',
            'order' => 0
        ]);

        $task2 = Task::create([
            'project_id' => $this->project->id,
            'team_id' => $this->team->id,
            'title' => 'Test task 2',
            'status' => 'todo',
            'priority' => 'high',
            'created_by' => $this->user->id
        ]);

        $response = $this->postJson("/projects/{$this->project->id}/kanban/tasks", [
            'kanban_column_id' => $column->id,
            'task_ids' => [$this->task->id, $task2->id],
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('kanbanTasks'));

        $this->assertDatabaseHas('kanban_tasks', [
            'kanban_column_id' => $column->id,
            'task_id' => $this->task->id,
            'order' => 0
        ]);

        $this->assertDatabaseHas('kanban_tasks', [
            'kanban_column_id' => $column->id,
            'task_id' => $task2->id,
            'order' => 1
        ]);
    }
}
