<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperBehaviorScore
 */
class BehaviorScore extends Model
{
    public $timestamps = false; // tabel behavior_scores tidak punya created_at/updated_at

    protected $guarded = [];

    public function assessment(): BelongsTo { return $this->belongsTo(BehaviorAssessment::class, 'assessment_id'); }
    public function aspect(): BelongsTo { return $this->belongsTo(BehaviorAspect::class, 'aspect_id'); }
}