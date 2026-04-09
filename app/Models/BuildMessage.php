<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildMessage extends Model
{
    protected $fillable = [
        'build_id',
        'user_id',
        'message',
    ];

    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
