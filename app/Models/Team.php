<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @extends Model
 *
 * @property-read User|null $owner
 * @property-read Collection<int, User> $members
 * @property-read Collection<int, Build> $builds
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Team> owner()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User> members()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Build, \App\Models\Team> builds()
 */
class Team extends Model
{
    protected $fillable = [
        'name',
        'owner_id',
    ];

    /**
     * @return BelongsTo<User, Team>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Build, Team>
     */
    public function builds(): HasMany
    {
        return $this->hasMany(Build::class);
    }
}
