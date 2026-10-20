<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\ClassRosterController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\LearningResourceController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TeachingRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/masuk', [LoginController::class, 'create'])->name('login');
Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/demo', [DemoController::class, 'index'])->name('demo.index');
Route::post('/demo/masuk/{user}', [DemoController::class, 'switch'])->name('demo.switch');
Route::post('/demo/keluar', [DemoController::class, 'logout'])->name('demo.logout');

// Prototipe interaktif publik untuk demo alur layar kepada calon sekolah.
Route::get('/prototipe', fn () => response()->file(public_path('prototipe/index.html')))
    ->name('prototype.index');

Route::middleware(['auth', 'permission:dashboard.view'])->group(function () {
    Route::get('/', HomeController::class)->name('home');

    // Guru
    Route::get('/guru', [GuruController::class, 'home'])->middleware('role:guru')->name('guru.home');

    // Orang tua dan siswa
    Route::get('/anak-saya', [GuardianController::class, 'index'])->middleware('role:orangtua')->name('guardian.index');
    Route::get('/rapor-saya', [ReportController::class, 'me'])->middleware('role:siswa')->name('student.home');
    Route::get('/siswa/{student}/laporan', [ReportController::class, 'show'])
        ->middleware('role:admin|kepsek|guru|orangtua|siswa')->name('report.show');

    // Admin dan kepala sekolah
    Route::get('/data-master', [MasterDataController::class, 'index'])->middleware('permission:master.view')->name('master-data.index');
    Route::get('/data-master/{section}', [MasterDataController::class, 'index'])->whereIn('section', ['kelas', 'siswa', 'guru', 'mapel', 'penugasan'])->middleware('permission:master.view')->name('master-data.section');
    Route::get('/riwayat-perubahan', [AuditLogController::class, 'index'])->middleware('permission:audit.view')->name('audit.index');
    Route::get('/wali-kelas', [ClassRosterController::class, 'index'])->middleware('permission:master.view')->name('class-roster.index');
    Route::post('/wali-kelas/siswa', [ClassRosterController::class, 'store'])->middleware('permission:master.manage')->name('class-roster.store');
    Route::post('/data-master/kelas', [MasterDataController::class, 'storeClass'])->middleware('permission:master.manage')->name('master-data.classes.store');
    Route::get('/data-master/kelas/{class}/edit', [MasterDataController::class, 'editClass'])->middleware('permission:master.manage')->name('master-data.classes.edit');
    Route::put('/data-master/kelas/{class}', [MasterDataController::class, 'updateClass'])->middleware('permission:master.manage')->name('master-data.classes.update');
    Route::delete('/data-master/kelas/{class}', [MasterDataController::class, 'destroyClass'])->middleware('permission:master.manage')->name('master-data.classes.destroy');
    Route::get('/data-master/kelas/export', [MasterDataController::class, 'exportClasses'])->middleware('permission:master.manage')->name('master-data.classes.export');
    Route::get('/data-master/guru/export', [MasterDataController::class, 'exportTeachers'])->middleware('permission:master.manage')->name('master-data.teachers.export');
    Route::get('/data-master/mapel/export', [MasterDataController::class, 'exportSubjects'])->middleware('permission:master.manage')->name('master-data.subjects.export');
    Route::get('/data-master/penugasan/export', [MasterDataController::class, 'exportAssignments'])->middleware('permission:master.manage')->name('master-data.assignments.export');
    Route::get('/data-master/siswa/export', [MasterDataController::class, 'exportStudents'])->middleware('permission:master.manage')->name('master-data.students.export');
    Route::get('/data-master/siswa/template-csv', [MasterDataController::class, 'studentImportTemplate'])->middleware('permission:master.manage')->name('master-data.students.template');
    Route::post('/data-master/siswa/impor', [MasterDataController::class, 'importStudents'])->middleware('permission:master.manage')->name('master-data.students.import');
    Route::get('/data-master/upload-template/{section}', [MasterDataController::class, 'masterImportTemplate'])->middleware('permission:master.manage')->name('master-data.import-template');
    Route::post('/data-master/import/{section}', [MasterDataController::class, 'importMasterData'])->middleware('permission:master.manage')->name('master-data.import');
    Route::post('/data-master/siswa', [MasterDataController::class, 'storeStudent'])->middleware('permission:master.manage')->name('master-data.students.store');
    Route::get('/data-master/siswa/{student}/edit', [MasterDataController::class, 'editStudent'])->middleware('permission:master.manage')->name('master-data.students.edit');
    Route::put('/data-master/siswa/{student}', [MasterDataController::class, 'updateStudent'])->middleware('permission:master.manage')->name('master-data.students.update');
    Route::delete('/data-master/siswa/{student}', [MasterDataController::class, 'destroyStudent'])->middleware('permission:master.manage')->name('master-data.students.destroy');
    Route::post('/data-master/mapel', [MasterDataController::class, 'storeSubject'])->middleware('permission:master.manage')->name('master-data.subjects.store');
    Route::get('/data-master/mapel/{subject}/edit', [MasterDataController::class, 'editSubject'])->middleware('permission:master.manage')->name('master-data.subjects.edit');
    Route::put('/data-master/mapel/{subject}', [MasterDataController::class, 'updateSubject'])->middleware('permission:master.manage')->name('master-data.subjects.update');
    Route::post('/data-master/guru', [MasterDataController::class, 'storeTeacher'])->middleware('permission:master.manage')->name('master-data.teachers.store');
    Route::get('/data-master/guru/{teacher}/edit', [MasterDataController::class, 'editTeacher'])->middleware('permission:master.manage')->name('master-data.teachers.edit');
    Route::put('/data-master/guru/{teacher}', [MasterDataController::class, 'updateTeacher'])->middleware('permission:master.manage')->name('master-data.teachers.update');
    Route::post('/data-master/penugasan', [MasterDataController::class, 'storeAssignment'])->middleware('permission:master.manage')->name('master-data.assignments.store');
    Route::get('/data-master/penugasan/{assignment}/edit', [MasterDataController::class, 'editAssignment'])->middleware('permission:master.manage')->name('master-data.assignments.edit');
    Route::delete('/data-master/penugasan/{assignment}', [MasterDataController::class, 'destroyAssignment'])->middleware('permission:master.manage')->name('master-data.assignments.destroy');

    Route::get('/nilai', [TeachingRecordController::class, 'grades'])->middleware('permission:grades.view')->name('grades.index');
    Route::post('/nilai', [TeachingRecordController::class, 'saveGrades'])->middleware('permission:grades.manage')->name('grades.store');
    Route::get('/absensi', [TeachingRecordController::class, 'attendance'])->middleware('permission:student-attendance.view')->name('attendance.index');
    Route::post('/absensi', [TeachingRecordController::class, 'saveAttendance'])->middleware('permission:student-attendance.manage')->name('attendance.store');

    Route::get('/perilaku', [BehaviorController::class, 'index'])->middleware('permission:behavior.view')->name('behavior.index');
    Route::post('/perilaku', [BehaviorController::class, 'save'])->middleware('permission:behavior.manage')->name('behavior.store');
    Route::post('/perilaku/periode', [BehaviorController::class, 'savePeriod'])->middleware('permission:settings.manage')->name('behavior.period.store');
    Route::post('/perilaku/aspek', [BehaviorController::class, 'storeAspect'])->middleware('permission:settings.manage')->name('behavior.aspects.store');
    Route::put('/perilaku/aspek/{aspect}', [BehaviorController::class, 'updateAspect'])->middleware('permission:settings.manage')->name('behavior.aspects.update');

    Route::get('/pengaturan', [SettingsController::class, 'index'])->middleware('permission:settings.manage')->name('settings.index');
    Route::post('/pengaturan/nilai-standar', [SettingsController::class, 'updateAcademicDefaults'])->middleware('permission:settings.manage')->name('settings.academic.update');
    Route::post('/pengaturan/kpi', [SettingsController::class, 'updateKpiSettings'])->middleware('permission:settings.manage')->name('settings.kpi.update');
    Route::put('/pengaturan/kategori-kpi', [SettingsController::class, 'updateKpiCategories'])->middleware('permission:settings.manage')->name('settings.kpi-categories.update');
    Route::post('/pengaturan/batas-edit', [SettingsController::class, 'updateEditWindows'])->middleware('permission:settings.manage')->name('settings.edit-windows.update');
    Route::post('/pengaturan/bobot-mapel', [SettingsController::class, 'updateSubjectWeight'])->middleware('permission:settings.manage')->name('settings.subject-weight.update');

    Route::get('/semester', [SemesterController::class, 'index'])->middleware('permission:master.view')->name('semesters.index');
    Route::post('/semester', [SemesterController::class, 'store'])->middleware('permission:master.manage')->name('semesters.store');
    Route::put('/semester/{semester}', [SemesterController::class, 'update'])->middleware('permission:master.manage')->name('semesters.update');
    Route::post('/semester/{semester}/aktif', [SemesterController::class, 'activate'])->middleware('permission:master.manage')->name('semesters.activate');

    Route::get('/kpi', [KpiController::class, 'index'])->middleware('permission:kpi.view')->name('kpi.index');
    Route::post('/kpi/hitung', [KpiController::class, 'hitung'])->middleware('permission:kpi.calculate')->name('kpi.hitung');
    Route::post('/kpi/publikasi', [KpiController::class, 'publikasi'])->middleware('permission:kpi.publish')->name('kpi.publikasi');

    Route::get('/materi-library', fn () => to_route('learning-resources.materials'));

    Route::get('/materi', [LearningResourceController::class, 'materials'])->name('learning-resources.materials');
    Route::get('/materi/{resource}/buka', [LearningResourceController::class, 'openMaterial'])->name('learning-resources.materials.open');
    Route::post('/materi', [LearningResourceController::class, 'storeMaterial'])->middleware('role:admin|guru')->name('learning-resources.materials.store');
    Route::put('/materi/{resource}', [LearningResourceController::class, 'updateMaterial'])->middleware('role:admin')->name('learning-resources.materials.update');
    Route::delete('/materi/{resource}', [LearningResourceController::class, 'destroyMaterial'])->middleware('role:admin')->name('learning-resources.materials.destroy');

    Route::get('/library', [LearningResourceController::class, 'library'])->middleware('role:admin|kepsek|guru|siswa')->name('learning-resources.library');
    Route::get('/library/{resource}/buka', [LearningResourceController::class, 'openLibrary'])->middleware('role:admin|kepsek|guru|siswa')->name('learning-resources.library.open');
    Route::post('/library', [LearningResourceController::class, 'storeLibrary'])->middleware('role:admin')->name('learning-resources.library.store');
    Route::put('/library/{resource}', [LearningResourceController::class, 'updateLibrary'])->middleware('role:admin')->name('learning-resources.library.update');
    Route::delete('/library/{resource}', [LearningResourceController::class, 'destroyLibrary'])->middleware('role:admin')->name('learning-resources.library.destroy');
});
