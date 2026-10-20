<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperGrade
 */
class Grade extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'original_score' => 'float',
            'is_remedial' => 'boolean',
            'assessed_on' => 'date',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function inputBy(): BelongsTo { return $this->belongsTo(User::class, 'input_by'); }
}