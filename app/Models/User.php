<?php

namespace App\Models;

use App\Services\SupabaseUserService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @extends Authenticatable
 *
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read Team|null $currentTeam
 * @property-read Collection<int, Team> $teams
 * @property-read Collection<int, Build> $builds
 * @property-read Collection<int, Build> $buildMemberships
 * @property-read Collection<int, BuildShare> $buildShares
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
     * @return HasMany<Team, User>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * @return BelongsTo<Team, User>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * @return BelongsToMany<Team>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Build, User>
     */
    public function builds(): HasMany
    {
        return $this->hasMany(Build::class, 'created_by');
    }

    /**
     * @return BelongsToMany<Build>
     */
    public function buildMemberships(): BelongsToMany
    {
        return $this->belongsToMany(Build::class, 'build_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<BuildShare, User>
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
