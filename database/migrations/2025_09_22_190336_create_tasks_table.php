<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Foreign key to projects table
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Foreign key to users table for assignee
            $table->foreignId('assignee_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('task_parent_id')
                ->nullable()
                ->constrained('tasks')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Task details
            $table->string('title');
            $table->text('description')->nullable();

            //Task time tracking
            $table->float('estimated_hours')->default(0);
            $table->float('time_spent')->default(0);

            //Task attributes, in the future these could be moved to separate tables for more flexibility
            $table->enum('status', ['todo', 'in-progress', 'done', 'blocked'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->enum('type', ['feature', 'bug', 'improvement'])->default('feature');

            //Task timelines
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            //Progress tracking
            $table->unsignedTinyInteger('progress')->default(0)->max(100);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['assignee_id']);
            $table->dropForeign(['task_parent_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('tasks');
    }
};
