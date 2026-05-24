<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @extends \Illuminate\Database\Eloquent\Model
 *
 * @property-read \App\Models\Build|null $build
 * @property-read \App\Models\User|null $user
 *
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Build, \App\Models\BuildMessage> build()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\BuildMessage> user()
 */
class BuildMessage extends Model
{
    protected $fillable = [
        'build_id',
        'user_id',
        'message',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Build, \App\Models\BuildMessage>
     */
    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\BuildMessage>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
