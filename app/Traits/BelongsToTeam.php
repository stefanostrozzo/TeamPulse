<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTeam
{
    protected static function bootBelongsToTeam()
    {
        static::creating(function ($model) {
            if (empty($model->team_id) && Auth::check()) {
                $model->team_id = Auth::user()->current_team_id;
            }
        });

        static::addGlobalScope('team_filter', function (Builder $builder) {
            if (Auth::check()) {
                if (!Auth::user()->hasRole('superadmin')) {
                    $builder->where('team_id', Auth::user()->current_team_id);
                }
            }
        });
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class);
    }
}
