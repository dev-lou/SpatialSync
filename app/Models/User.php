<?php

namespace App\Models;

use App\Services\SupabaseUserService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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

    // Relationships (these still work with local SQLite for now)
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function builds(): HasMany
    {
        return $this->hasMany(Build::class, 'created_by');
    }

    public function buildMemberships(): BelongsToMany
    {
        return $this->belongsToMany(Build::class, 'build_members')
            ->withPivot('role')
            ->withTimestamps();
    }

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
