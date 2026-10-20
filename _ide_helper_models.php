<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAuditLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAspect whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBehaviorAspect {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $semester_id
 * @property int $student_id
 * @property int $teacher_id
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BehaviorScore> $scores
 * @property-read int|null $scores_count
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorAssessment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBehaviorAssessment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $semester_id
 * @property \Illuminate\Support\Carbon $opens_at
 * @property \Illuminate\Support\Carbon $closes_at
 * @property bool $is_locked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Semester $semester
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod whereClosesAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod whereOpensAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorPeriod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBehaviorPeriod {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $assessment_id
 * @property int $aspect_id
 * @property int $score
 * @property-read \App\Models\BehaviorAspect $aspect
 * @property-read \App\Models\BehaviorAssessment $assessment
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorScore query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorScore whereAspectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorScore whereAssessmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BehaviorScore whereScore($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBehaviorScore {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $class_id
 * @property string $academic_year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SchoolClass $schoolClass
 * @property-read \App\Models\Student|null $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment whereAcademicYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Enrollment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperEnrollment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $class_id
 * @property int $subject_id
 * @property int $semester_id
 * @property string $type
 * @property int $sequence
 * @property string|null $title
 * @property float $score
 * @property bool $is_remedial
 * @property float|null $original_score
 * @property \Illuminate\Support\Carbon|null $assessed_on
 * @property int $input_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $inputBy
 * @property-read \App\Models\SchoolClass $schoolClass
 * @property-read \App\Models\Semester $semester
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\Subject $subject
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereAssessedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereInputBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereIsRemedial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereOriginalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereUpdatedBy($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGrade {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $semester_id
 * @property int $subject_id
 * @property int $weight_uh
 * @property int $weight_uts
 * @property int $weight_uas
 * @property float $kkm
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereKkm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereWeightUas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereWeightUh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GradeWeight whereWeightUts($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGradeWeight {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guardian withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGuardian {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $semester_id
 * @property int $student_id
 * @property int $teacher_id
 * @property string $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HomeroomNote whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperHomeroomNote {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property float $min_score
 * @property float $max_score
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory whereMaxScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory whereMinScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KpiCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperKpiCategory {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $grade_level
 * @property string $academic_year
 * @property int|null $homeroom_teacher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeachingAssignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Enrollment> $enrollments
 * @property-read int|null $enrollments_count
 * @property-read \App\Models\Teacher|null $homeroomTeacher
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereAcademicYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereGradeLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereHomeroomTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSchoolClass {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $academic_year
 * @property int $term
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereAcademicYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereTerm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSemester {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property array<array-key, mixed> $value
 * @property string|null $description
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSetting {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $nis
 * @property string|null $nisn
 * @property string $name
 * @property string $gender
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudentAttendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Enrollment> $enrollments
 * @property-read int|null $enrollments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Guardian> $guardians
 * @property-read int|null $guardians_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudentKpi> $kpis
 * @property-read int|null $kpis_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNisn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperStudent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $class_id
 * @property int $subject_id
 * @property int $semester_id
 * @property \Illuminate\Support\Carbon $date
 * @property int $lesson_period
 * @property string $status
 * @property string|null $note
 * @property string $source
 * @property int $recorded_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $recordedBy
 * @property-read \App\Models\SchoolClass $schoolClass
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\Subject $subject
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereLessonPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentAttendance whereUpdatedBy($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperStudentAttendance {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $semester_id
 * @property int $class_id
 * @property float|null $grade_score
 * @property float|null $attendance_score
 * @property float|null $behavior_score
 * @property int $weight_grade
 * @property int $weight_attendance
 * @property int $weight_behavior
 * @property float|null $final_score
 * @property string|null $category
 * @property float|null $previous_final_score
 * @property array<array-key, mixed>|null $breakdown
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $calculated_at
 * @property int|null $ready_by
 * @property \Illuminate\Support\Carbon|null $ready_at
 * @property int|null $published_by
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SchoolClass $schoolClass
 * @property-read \App\Models\Semester $semester
 * @property-read \App\Models\Student|null $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereAttendanceScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereBehaviorScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereCalculatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereFinalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereGradeScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi wherePreviousFinalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereReadyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereReadyBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereWeightAttendance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereWeightBehavior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentKpi whereWeightGrade($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperStudentKpi {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $code
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSubject {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $nip
 * @property string $name
 * @property string|null $phone
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeachingAssignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SchoolClass> $homeroomClasses
 * @property-read int|null $homeroom_classes_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTeacher {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $teacher_id
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $check_in_at
 * @property \Illuminate\Support\Carbon|null $check_out_at
 * @property string $status
 * @property string|null $note
 * @property string $source
 * @property int|null $leave_request_id
 * @property int|null $recorded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereCheckInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereCheckOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereLeaveRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherAttendance whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTeacherAttendance {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $teacher_id
 * @property int $class_id
 * @property int $subject_id
 * @property int $semester_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SchoolClass $schoolClass
 * @property-read \App\Models\Semester $semester
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeachingAssignment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTeachingAssignment {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $password
 * @property string $role
 * @property bool $is_active
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Guardian|null $guardian
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

