<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperBehaviorPeriod
 */
class BehaviorPeriod extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['opens_at' => 'datetime', 'closes_at' => 'datetime', 'is_locked' => 'boolean'];
    }

    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }

    public function isOpen(): bool
    {
        return ! $this->is_locked && now()->between($this->opens_at, $this->closes_at);
    }
}