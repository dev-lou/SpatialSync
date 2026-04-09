<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartPreset extends Model
{
    protected $fillable = [
        'name',
        'type',
        'variant',
        'default_width',
        'default_height',
        'default_depth',
        'default_color',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'default_width' => 'float',
        'default_height' => 'float',
        'default_depth' => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
