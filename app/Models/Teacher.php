<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperTeacher
 */
class Teacher extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function homeroomClasses(): HasMany { return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id'); }
    public function assignments(): HasMany { return $this->hasMany(TeachingAssignment::class); }
}