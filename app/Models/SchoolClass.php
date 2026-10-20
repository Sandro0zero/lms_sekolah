<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperSchoolClass
 */
class SchoolClass extends Model
{
    protected $table = 'school_classes';
    protected $guarded = [];

    public function homeroomTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'homeroom_teacher_id'); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class, 'class_id'); }
    public function assignments(): HasMany { return $this->hasMany(TeachingAssignment::class, 'class_id'); }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments', 'class_id', 'student_id');
    }
}