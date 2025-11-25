<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_id',
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

    // Relazioni
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }

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

    // Accessori
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