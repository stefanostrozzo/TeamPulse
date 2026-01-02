<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, BelongsToTeam;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'progress',
        'color',
        'tags'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'integer',
        'tags' => 'array'
    ];

    /**
     * Retrieves the tasks of the current project
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     *
     * @return BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')->withTimestamps();
    }

    /**
     * Retrives the team of the project
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    //Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    // Accessories
    public function getProgressPercentageAttribute(): float
    {
        return $this->progress ?? 0;
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) return null;

        return now()->diffInDays($this->end_date, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->end_date && now()->gt($this->end_date);
    }
}
