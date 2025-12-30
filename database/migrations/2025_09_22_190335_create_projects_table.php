<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            //Project basic details
            $table->string('name');
            $table->text('description')->nullable();

            //Project status and priority, in the future this could be references to separate tables
            $table->enum('status', ['active', 'completed', 'on-hold', 'archived'])->default('on-hold');
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');

            //Project timeline
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Progress in percentage
            $table->unsignedTinyInteger('progress')->default(0)->max(100);

            $table->timestamps();
        });

        //Pivot table for project members with different roles managed by Spatie
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->nullable()
                ->constrained('roles')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['project_id', 'user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['role_id']);
        });

        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
