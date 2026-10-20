<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateAdmin extends Command
{
    protected $signature = 'lms:create-admin';

    protected $description = 'Buat akun administrator awal dengan kata sandi yang dimasukkan secara aman';

    public function handle(): int
    {
        if (! Role::where('name', 'admin')->where('guard_name', 'web')->exists()) {
            $this->error('Peran belum tersedia. Jalankan migrasi lalu seed RolePermissionSeeder terlebih dahulu.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama administrator'));
        $username = trim((string) $this->ask('Nama pengguna'));
        $email = trim((string) $this->ask('Email (opsional)')) ?: null;

        if ($name === '' || $username === '') {
            $this->error('Nama dan nama pengguna wajib diisi.');

            return self::FAILURE;
        }

        if (User::withTrashed()->where('username', $username)->exists()) {
            $this->error('Nama pengguna tersebut sudah dipakai.');

            return self::FAILURE;
        }

        if ($email && User::withTrashed()->where('email', $email)->exists()) {
            $this->error('Email tersebut sudah dipakai.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Kata sandi (minimal 12 karakter)');
        $confirmation = (string) $this->secret('Ulangi kata sandi');

        if (strlen($password) < 12 || ! hash_equals($password, $confirmation)) {
            $this->error('Kata sandi harus minimal 12 karakter dan kedua isian harus sama.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'role' => 'admin',
            'is_active' => true,
            'password' => $password,
        ]);
        $user->assignRole('admin');

        $this->info("Akun administrator '{$username}' berhasil dibuat.");

        return self::SUCCESS;
    }
}
