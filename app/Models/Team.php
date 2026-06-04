<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    /**
     * Retrieves the users of the team
     * @return BelongsToMany
     */
    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'team_user')
        ->withPivot('role');
    }

    /**
     * Retrieves project of the selected team
     * @return HasMany
     */
    public function projects(): HasMany {
        return $this->hasMany(Project::class);
    }

    /**
     * Retrieve all pending invitations associated with this team.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function getRole(User $user) : string
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()->pivot;
        return $pivot->role;
    }
}
