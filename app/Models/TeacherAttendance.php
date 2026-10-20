<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTeacherAttendance
 */
class TeacherAttendance extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['date' => 'date', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
}