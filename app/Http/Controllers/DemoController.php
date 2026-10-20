<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pemilih pengguna sementara untuk pengembangan dan demo.
 * Hanya aktif jika APP_ENV=local.
 */
class DemoController extends Controller
{
    private function guard(): void
    {
        abort_unless(app()->environment('local'), 404);
    }

    public function index()
    {
        $this->guard();

        $users = User::where('is_active', true)->orderBy('name')->get()->groupBy('role');

        // Keterangan tambahan agar mudah memilih: wali kelas dan nama anak
        $homeroom = SchoolClass::with('homeroomTeacher')->get()
            ->filter(fn ($c) => $c->homeroomTeacher)
            ->groupBy(fn ($c) => $c->homeroomTeacher->user_id)
            ->map(fn ($g) => 'wali kelas ' . $g->pluck('name')->implode(', '));

        $children = Guardian::with('students')->get()
            ->mapWithKeys(fn ($g) => [$g->user_id => 'anak: ' . $g->students->pluck('name')->implode(', ')]);

        return view('demo.index', [
            'users' => $users,
            'hints' => $homeroom->union($children),
            'roles' => [
                'admin' => 'Admin',
                'kepsek' => 'Kepala Sekolah',
                'guru' => 'Guru',
                'orangtua' => 'Orang Tua',
                'siswa' => 'Siswa',
            ],
        ]);
    }

    public function switch(Request $request, User $user)
    {
        $this->guard();
        abort_unless($user->is_active, 403);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        $this->guard();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('demo.index');
    }
}
