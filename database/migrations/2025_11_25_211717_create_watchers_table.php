<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_watchers', function (Blueprint $table) {
            $table->id();

            // Foreign key to tasks table
            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            // Foreign key to user table
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unique(['task_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_watchers', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('task_watchers');
    }
};
