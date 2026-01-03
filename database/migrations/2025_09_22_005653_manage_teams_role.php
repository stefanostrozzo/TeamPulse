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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamps();
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('role')->default('member');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_team_id')->nullable()->constrained('teams')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');

        Schema::table('team_user', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('team_user');

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
        });
        Schema::dropIfExists('invitations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_team_id']);
        });
    }
};
