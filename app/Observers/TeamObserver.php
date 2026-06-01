<?php
namespace App\Observers;

use App\Models\Team;
use App\Models\TaskType;

class TeamObserver
{
    public function created(Team $team): void
    {
        foreach ([
            ['name' => 'Funzionalità',  'color' => '#07b4f6'],
            ['name' => 'Bug',            'color' => '#ef4444'],
            ['name' => 'Miglioramento',  'color' => '#10b981'],
        ] as $type) {
            TaskType::create(['team_id' => $team->id, 'name' => $type['name'], 'color' => $type['color']]);
        }
    }
}
