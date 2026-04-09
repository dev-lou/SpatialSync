<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'build_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function parts(): HasMany
    {
        return $this->hasMany(BuildPart::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(BuildShare::class);
    }

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
        return in_array($this->userRole($user), ['admin', 'editor']);
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
