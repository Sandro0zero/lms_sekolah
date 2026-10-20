<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        return Auth::check()
            ? redirect()->route('home')
            : view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $identifier = trim($validated['login']);
        $user = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($identifier) {
                $query->where('username', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();

        if (! $user || ! $user->password || ! Hash::check($validated['password'], $user->password)) {
            $guardian = Guardian::query()
                ->where('name', $identifier)
                ->whereHas('students', fn ($query) => $query->where('nis', $validated['password']))
                ->with('user')
                ->first();
            $guardianUser = $guardian?->user;

            if (! $guardianUser || ! $guardianUser->is_active) {
                return back()
                    ->withErrors(['auth' => 'Nama pengguna/email atau kata sandi tidak cocok.'])
                    ->onlyInput('login');
            }

            $user = $guardianUser;
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $destination = match (true) {
            $user->hasAnyRole('admin', 'kepsek') => route('kpi.index'),
            $user->hasRole('orangtua') => route('guardian.index'),
            default => route('home'),
        };

        return redirect()->intended($destination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Kamu sudah keluar dari aplikasi.');
    }
}
