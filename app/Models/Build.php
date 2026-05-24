<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @extends \Illuminate\Database\Eloquent\Model
 *
 * @property-read \App\Models\Team|null $team
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BuildPart> $parts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BuildShare> $shares
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BuildMessage> $messages
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Team, \App\Models\Build> team()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Build> creator()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User> members()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BuildPart, \App\Models\Build> parts()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BuildShare, \App\Models\Build> shares()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BuildMessage, \App\Models\Build> messages()
 */
class Build extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'description',
        'canvas_json',
        'created_by',
        'current_floor',
        'roof_visible',
    ];

    protected $casts = [
        'canvas_json' => 'array',
        'current_floor' => 'integer',
        'roof_visible' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Team, \App\Models\Build>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Build>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'build_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BuildPart, \App\Models\Build>
     */
    public function parts(): HasMany
    {
        return $this->hasMany(BuildPart::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BuildShare, \App\Models\Build>
     */
    public function shares(): HasMany
    {
        return $this->hasMany(BuildShare::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BuildMessage, \App\Models\Build>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(BuildMessage::class);
    }

    public function userRole(User $user): ?string
    {
        if ($user->is_admin) {
            return 'admin';
        }

        $member = $this->members()->where('user_id', $user->id)->first();

        return $member?->pivot?->role;
    }

    public function canEdit(User $user): bool
    {
        return in_array($this->userRole($user), ['admin', 'editor'], true);
    }

    public function isAdmin(User $user): bool
    {
        return $user->is_admin || $this->userRole($user) === 'admin';
    }

    public function isEditor(User $user): bool
    {
        return $this->userRole($user) === 'editor';
    }

    public function isViewer(User $user): bool
    {
        return $this->userRole($user) === 'viewer';
    }
}
