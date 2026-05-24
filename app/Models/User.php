<?php

namespace App\Models;

use App\Services\SupabaseUserService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @extends \Illuminate\Foundation\Auth\User
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ownedTeams
 * @property-read \App\Models\Team|null $currentTeam
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Build> $builds
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Build> $buildMemberships
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BuildShare> $buildShares
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id',
        'is_admin',
        'plan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Team, \App\Models\User>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Team, \App\Models\User>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Team>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Build, \App\Models\User>
     */
    public function builds(): HasMany
    {
        return $this->hasMany(Build::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Build>
     */
    public function buildMemberships(): BelongsToMany
    {
        return $this->belongsToMany(Build::class, 'build_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\BuildShare, \App\Models\User>
     */
    public function buildShares(): HasMany
    {
        return $this->hasMany(BuildShare::class);
    }

    // Override to sync with Supabase on save
    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Also create in Supabase
            $supabaseUser = app(SupabaseUserService::class)->create([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password,
                'is_admin' => $user->is_admin ?? false,
                'plan' => $user->plan ?? 'free',
            ]);

            if ($supabaseUser && isset($supabaseUser['id'])) {
                $user->supabase_id = $supabaseUser['id'];
            }
        });
    }

    protected $appends = ['supabase_id'];

    public function getSupabaseIdAttribute()
    {
        // We'll store Supabase ID in a separate column or use email as identifier
        return null;
    }
}
