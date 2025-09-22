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
            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('status', ['active', 'completed', 'on-hold', 'archived'])->default('active');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('color')->nullable();
            $table->json('tags')->nullable();

            $table->timestamps();
        });

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

            $table->string('role')->nullable();
            
            $table->timestamps();

            $table->unique(['project_id', 'user_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
