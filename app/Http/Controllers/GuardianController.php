<?php

namespace App\Http\Controllers;

use App\Models\Semester;

class GuardianController extends Controller
{
    public function index()
    {
        $user = $this->user();
        abort_unless($user->hasRole('orangtua') && $user->guardian, 403);

        $semester = Semester::active();
        abort_unless($semester, 404, 'Belum ada semester aktif.');

        $students = $user->guardian->students()->orderBy('name')->get();

        // Satu anak: langsung ke laporannya
        if ($students->count() === 1) {
            return redirect()->route('report.show', $students->first());
        }

        $children = $students->map(fn ($s) => [
            'student' => $s,
            'class' => $s->classForYear($semester->academic_year),
        ]);

        return view('guardian.index', compact('children', 'semester'));
    }
}