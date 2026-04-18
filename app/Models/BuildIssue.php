<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BuildIssue extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'build_id',
        'part_id',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'position_x',
        'position_y',
        'position_z',
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'position_z' => 'float',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(BuildPart::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Open issues only
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope: Resolved issues
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope: By priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: By status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => '#ef4444', // red
            'in_progress' => '#eab308', // yellow
            'resolved' => '#22c55e', // green
            'closed' => '#6b7280', // gray
            default => '#6b7280',
        };
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => '#dc2626', // red-600
            'high' => '#f97316', // orange-500
            'medium' => '#eab308', // yellow-500
            'low' => '#22c55e', // green-500
            default => '#6b7280',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => ucfirst($this->priority),
        };
    }

    /**
     * Is issue open?
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    /**
     * Is issue resolved?
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Get 3D position as array
     */
    public function getPositionAttribute(): ?array
    {
        if ($this->position_x === null || $this->position_y === null || $this->position_z === null) {
            return null;
        }

        return [
            'x' => $this->position_x,
            'y' => $this->position_y,
            'z' => $this->position_z,
        ];
    }
}
