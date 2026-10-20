<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperStudent
 */
class Student extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'registration_date' => 'date'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }
    public function guardians(): BelongsToMany { return $this->belongsToMany(Guardian::class, 'guardian_student'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function attendances(): HasMany { return $this->hasMany(StudentAttendance::class); }
    public function kpis(): HasMany { return $this->hasMany(StudentKpi::class); }

    /** Kelas siswa pada tahun ajaran tertentu (contoh: "2026/2027"). */
    public function classForYear(string $academicYear): ?SchoolClass
    {
        return SchoolClass::whereHas('enrollments', fn ($q) => $q
            ->where('student_id', $this->id)
            ->where('academic_year', $academicYear))->first();
    }
}
