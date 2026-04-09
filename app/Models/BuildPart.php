<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildPart extends Model
{
    protected $fillable = [
        'build_id',
        'type',
        'variant',
        'position_x',
        'position_y',
        'position_z',
        'width',
        'height',
        'depth',
        'rotation_y',
        'color',
        'color_front',
        'color_back',
        'material',
        'shape_points',
        'floor_number',
        'z_index',
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'position_z' => 'float',
        'width' => 'float',
        'height' => 'float',
        'depth' => 'float',
        'rotation_y' => 'integer',
        'shape_points' => 'array',
        'floor_number' => 'integer',
        'z_index' => 'integer',
    ];

    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class);
    }

    public function preset()
    {
        return PartPreset::where('type', $this->type)
            ->where('variant', $this->variant)
            ->first();
    }
}
