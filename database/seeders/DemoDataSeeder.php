<?php

namespace Database\Seeders;

use App\Models\BehaviorAspect;
use App\Models\BehaviorAssessment;
use App\Models\BehaviorPeriod;
use App\Models\BehaviorScore;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Guardian;
use App\Models\HomeroomNote;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeaveRequest;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh: 1 semester aktif, 3 kelas, 5 mapel, 5 guru, 30 siswa + orang tua.
 * Jalankan SETELAH `php artisan migrate:fresh` (seeder ini tidak aman dijalankan dua kali).
 * Semua akun demo memakai password: password
 *
 * Siswa pertama di tiap kelas sengaja dibuat "berisiko" (nilai rendah, sering bolos/alfa),
 * dan siswa terakhir di kelas 8A sengaja tidak punya nilai UAS Matematika, supaya
 * kasus "KPI belum lengkap" ikut teruji. Nilai dibuat dengan umur input berbeda-beda
 * (0-20 hari) agar aturan batas 7 hari bisa diuji.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);
        $this->call(DefaultSettingsSeeder::class);

        // --- Akun sekolah ---
        User::create(['name' => 'Admin Sekolah', 'username' => 'admin', 'role' => 'admin', 'password' => 'password']);
        User::create(['name' => 'Drs. Hendra Wijaya', 'username' => 'kepsek', 'role' => 'kepsek', 'password' => 'password']);

        // --- Semester ---
        $semester = Semester::create([
            'academic_year' => '2026/2027', 'term' => 1,
            'start_date' => '2026-07-13', 'end_date' => '2026-12-19', 'is_active' => true,
        ]);

        // --- Mapel dan guru (1 guru per mapel) ---
        $subjectNames = ['Matematika', 'IPA', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPS'];
        $teacherNames = ['Rina Susanti, S.Pd', 'Budi Santoso, S.Pd', 'Siti Aminah, S.Pd', 'Agus Prasetyo, S.Pd', 'Dewi Lestari, S.Pd'];
        $teacherLogin = ['rina', 'budi', 'siti', 'agus', 'dewi'];

        $subjects = [];
        $teachers = [];
        foreach ($subjectNames as $i => $name) {
            $subjects[$i] = Subject::create(['code' => 'MP' . ($i + 1), 'name' => $name]);
            $user = User::create(['name' => $teacherNames[$i], 'username' => $teacherLogin[$i], 'role' => 'guru', 'password' => 'password']);
            $teachers[$i] = Teacher::create(['user_id' => $user->id, 'nip' => '1980' . ($i + 1) . '0001', 'name' => $teacherNames[$i]]);

            GradeWeight::create([
                'semester_id' => $semester->id, 'subject_id' => $subjects[$i]->id,
                'weight_uh' => 40, 'weight_uts' => 30, 'weight_uas' => 30, 'kkm' => 75,
            ]);
        }

        // --- Kelas (wali kelas: guru ke-0, ke-1, ke-2) ---
        $classes = [];
        foreach ([['7A', 7, 0], ['7B', 7, 1], ['8A', 8, 2]] as $c => [$name, $level, $homeroomIdx]) {
            $classes[$c] = SchoolClass::create([
                'name' => $name, 'grade_level' => $level, 'academic_year' => '2026/2027',
                'homeroom_teacher_id' => $teachers[$homeroomIdx]->id,
            ]);
            foreach ($subjects as $i => $subject) {
                TeachingAssignment::create([
                    'teacher_id' => $teachers[$i]->id, 'class_id' => $classes[$c]->id,
                    'subject_id' => $subject->id, 'semester_id' => $semester->id,
                ]);
            }
        }

        // --- Siswa + orang tua ---
        $firstNames = ['Dimas', 'Ayu', 'Rizki', 'Putri', 'Fajar', 'Salsa', 'Bagas', 'Nadia', 'Arief', 'Citra',
            'Galih', 'Dinda', 'Hendra', 'Intan', 'Joko', 'Kirana', 'Lukman', 'Maya', 'Naufal', 'Olivia',
            'Pandu', 'Qonita', 'Raka', 'Sekar', 'Tegar', 'Umi', 'Vino', 'Wulan', 'Yoga', 'Zahra'];
        $lastNames = ['Pratama', 'Saputra', 'Wijaya', 'Kusuma', 'Nugroho', 'Hidayat'];

        $students = [];
        $guardians = [];
        foreach ($firstNames as $idx => $first) {
            $classIdx = intdiv($idx, 10);
            $nis = '2026' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT);
            $fullName = $first . ' ' . $lastNames[$idx % count($lastNames)];

            $sUser = User::create(['name' => $fullName, 'username' => 's' . $nis, 'role' => 'siswa', 'password' => 'password']);
            $student = Student::create([
                'user_id' => $sUser->id, 'nis' => $nis, 'name' => $fullName,
                'gender' => $idx % 2 === 0 ? 'L' : 'P',
            ]);
            Enrollment::create(['student_id' => $student->id, 'class_id' => $classes[$classIdx]->id, 'academic_year' => '2026/2027']);

            $gUser = User::create(['name' => 'Orang tua ' . $first, 'username' => 'ortu' . $nis, 'role' => 'orangtua', 'password' => 'password']);
            $guardian = Guardian::create(['user_id' => $gUser->id, 'name' => 'Orang tua ' . $first]);
            $guardian->students()->attach($student->id, ['relationship' => $idx % 2 === 0 ? 'ayah' : 'ibu']);

            $students[$idx] = $student;
            $guardians[$idx] = $guardian;
        }
        // Satu orang tua dengan dua anak (siswa ke-2 dan ke-3) untuk menguji pilihan anak
        $guardians[1]->students()->attach($students[2]->id, ['relationship' => 'ibu']);

        // --- Nilai ---
        $gradeRows = [];
        foreach ($students as $idx => $student) {
            $classId = $classes[intdiv($idx, 10)]->id;
            $risk = $idx % 10 === 0;

            foreach ($subjects as $si => $subject) {
                $teacherUserId = $teachers[$si]->user_id;
                $base = $risk ? mt_rand(45, 75) : mt_rand(65, 95);

                $entries = [['uh', 1], ['uh', 2], ['uh', 3], ['uts', 1], ['uas', 1]];
                foreach ($entries as [$type, $seq]) {
                    if ($idx === 29 && $si === 0 && $type === 'uas') {
                        continue; // sengaja kosong: kasus KPI belum lengkap
                    }
                    $score = max(30, min(100, $base + mt_rand(-8, 8)));
                    $created = now()->subDays(mt_rand(0, 20))->setTime(10, 0)->toDateTimeString();

                    $gradeRows[] = [
                        'student_id' => $student->id, 'class_id' => $classId,
                        'subject_id' => $subject->id, 'semester_id' => $semester->id,
                        'type' => $type, 'sequence' => $seq,
                        'title' => $type === 'uh' ? 'UH ' . $seq : strtoupper($type),
                        'score' => $score, 'is_remedial' => false,
                        'input_by' => $teacherUserId,
                        'created_at' => $created, 'updated_at' => $created,
                    ];
                }
            }
        }
        foreach (array_chunk($gradeRows, 500) as $chunk) {
            Grade::insert($chunk);
        }

        // --- Absensi siswa (10 hari sekolah terakhir, per mapel) ---
        $dates = [];
        $d = now()->startOfDay();
        while (count($dates) < 10) {
            if (! $d->isWeekend()) {
                $dates[] = $d->copy();
            }
            $d->subDay();
        }

        $notes = ['sakit' => 'Demam', 'izin' => 'Keperluan keluarga', 'alfa' => 'Tanpa keterangan', 'bolos' => 'Tidak mengikuti pelajaran, terlihat di kantin'];
        $attRows = [];
        foreach ($students as $idx => $student) {
            $classId = $classes[intdiv($idx, 10)]->id;
            $risk = $idx % 10 === 0;
            // batas kumulatif: hadir, sakit, izin, alfa, (sisanya bolos)
            $limits = $risk ? [70, 78, 84, 92] : [94, 96, 97, 98];

            foreach ($dates as $date) {
                foreach ($subjects as $si => $subject) {
                    $r = mt_rand(1, 100);
                    $status = $r <= $limits[0] ? 'hadir' : ($r <= $limits[1] ? 'sakit' : ($r <= $limits[2] ? 'izin' : ($r <= $limits[3] ? 'alfa' : 'bolos')));
                    $created = $date->copy()->setTime(7 + $si, 30)->toDateTimeString();

                    $attRows[] = [
                        'student_id' => $student->id, 'class_id' => $classId,
                        'subject_id' => $subject->id, 'semester_id' => $semester->id,
                        'date' => $date->toDateString(), 'lesson_period' => $si + 1,
                        'status' => $status, 'note' => $notes[$status] ?? null,
                        'source' => 'guru', 'recorded_by' => $teachers[$si]->user_id,
                        'created_at' => $created, 'updated_at' => $created,
                    ];
                }
            }
        }
        foreach (array_chunk($attRows, 500) as $chunk) {
            StudentAttendance::insert($chunk);
        }

        // --- Absensi guru ---
        $tRows = [];
        foreach ($teachers as $teacher) {
            foreach ($dates as $date) {
                $late = mt_rand(1, 100) <= 10;
                $in = $date->copy()->setTime(6, mt_rand(30, 55));
                if ($late) {
                    $in = $date->copy()->setTime(7, mt_rand(5, 25));
                }
                $tRows[] = [
                    'teacher_id' => $teacher->id, 'date' => $date->toDateString(),
                    'check_in_at' => $in->toDateTimeString(),
                    'check_out_at' => $date->copy()->setTime(14, mt_rand(0, 30))->toDateTimeString(),
                    'status' => $late ? 'terlambat' : 'hadir', 'source' => 'self',
                    'created_at' => $in->toDateTimeString(), 'updated_at' => $in->toDateTimeString(),
                ];
            }
        }
        TeacherAttendance::insert($tRows);

        TeacherLeaveRequest::create([
            'teacher_id' => $teachers[3]->id, 'date_from' => now()->addDay()->toDateString(),
            'date_to' => now()->addDays(2)->toDateString(), 'type' => 'izin',
            'reason' => 'Keperluan keluarga', 'status' => 'pending',
        ]);

        // --- Penilaian perilaku (periode sedang dibuka) ---
        BehaviorPeriod::create([
            'semester_id' => $semester->id,
            'opens_at' => now()->subDays(5), 'closes_at' => now()->addDays(10), 'is_locked' => false,
        ]);

        $aspectIds = BehaviorAspect::where('is_active', true)->pluck('id');
        $scoreRows = [];
        foreach ($students as $idx => $student) {
            $risk = $idx % 10 === 0;
            foreach ($teachers as $teacher) {
                $assessment = BehaviorAssessment::create([
                    'semester_id' => $semester->id, 'student_id' => $student->id,
                    'teacher_id' => $teacher->id, 'submitted_at' => now(),
                ]);
                foreach ($aspectIds as $aspectId) {
                    $scoreRows[] = [
                        'assessment_id' => $assessment->id, 'aspect_id' => $aspectId,
                        'score' => $risk ? mt_rand(2, 3) : mt_rand(3, 4),
                    ];
                }
            }

            HomeroomNote::create([
                'semester_id' => $semester->id, 'student_id' => $student->id,
                'teacher_id' => $classes[intdiv($idx, 10)]->homeroom_teacher_id,
                'note' => $risk
                    ? 'Perlu meningkatkan kehadiran dan fokus di kelas.'
                    : 'Perkembangan baik, pertahankan semangat belajar.',
            ]);
        }
        foreach (array_chunk($scoreRows, 500) as $chunk) {
            BehaviorScore::insert($chunk);
        }

        $this->call(RolePermissionSeeder::class);
    }
}
