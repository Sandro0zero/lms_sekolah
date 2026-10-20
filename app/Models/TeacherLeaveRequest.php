<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherLeaveRequest extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['date_from' => 'date', 'date_to' => 'date', 'reviewed_at' => 'datetime'];
    }

    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}