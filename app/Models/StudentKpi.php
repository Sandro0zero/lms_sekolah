<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperStudentKpi
 */
class StudentKpi extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'grade_score' => 'float',
            'attendance_score' => 'float',
            'behavior_score' => 'float',
            'final_score' => 'float',
            'previous_final_score' => 'float',
            'breakdown' => 'array',
            'calculated_at' => 'datetime',
            'ready_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}