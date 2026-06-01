<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_type_id')->nullable()->after('type')
                  ->constrained('task_types')->nullOnDelete();
        });

        $defaults = [
            'feature'     => ['name' => 'Funzionalità',  'color' => '#07b4f6'],
            'bug'         => ['name' => 'Bug',            'color' => '#ef4444'],
            'improvement' => ['name' => 'Miglioramento',  'color' => '#10b981'],
        ];

        foreach (DB::table('teams')->pluck('id') as $teamId) {
            $typeMap = [];
            foreach ($defaults as $enumVal => $meta) {
                $id = DB::table('task_types')->insertGetId([
                    'team_id' => $teamId, 'name' => $meta['name'],
                    'color' => $meta['color'], 'created_at' => now(), 'updated_at' => now(),
                ]);
                $typeMap[$enumVal] = $id;
            }
            foreach ($typeMap as $enumVal => $newId) {
                DB::table('tasks')->where('team_id', $teamId)->where('type', $enumVal)
                    ->update(['task_type_id' => $newId]);
            }
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('type', ['feature','bug','improvement'])->default('feature')->after('task_type_id');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_type_id']);
            $table->dropColumn('task_type_id');
        });
    }
};
