<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                ->constrained()
                ->cascadeOnDelete();

            // The team member who actually worked on the task
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Who created this entry (same as user_id for self-log; manager id when logged on behalf)
            $table->foreignId('logged_by_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable(); // null = active timer

            // Seconds of work; null until the timer is stopped or set directly on manual log
            $table->unsignedInteger('duration_seconds')->nullable();

            $table->string('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
