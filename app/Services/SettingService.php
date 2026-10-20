<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Membaca dan mengubah pengaturan dari tabel `settings` (kunci-nilai JSON).
 */
class SettingService
{
    private static ?array $cache = null;

    public function get(string $key, mixed $default = null): mixed
    {
        self::$cache ??= Setting::all()->pluck('value', 'key')->all();

        return array_key_exists($key, self::$cache) ? self::$cache[$key] : $default;
    }

    public function set(string $key, mixed $value, ?int $userId = null): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->updated_by = $userId;
        $setting->save();

        self::$cache = null;
    }
}