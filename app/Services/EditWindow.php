<?php

namespace App\Services;

use Carbon\CarbonInterface;

/**
 * Aturan batas hari ubah data (default 7 hari sejak input PERTAMA).
 * Dasarnya created_at, bukan updated_at, jadi mengedit tidak memperpanjang batas.
 */
class EditWindow
{
    public function __construct(private SettingService $settings)
    {
    }

    public function days(string $settingKey): int
    {
        return (int) $this->settings->get($settingKey, 7);
    }

    public function isOpen(?CarbonInterface $createdAt, string $settingKey): bool
    {
        if ($createdAt === null) {
            return true; // data baru belum tersimpan
        }

        return now()->lessThanOrEqualTo($createdAt->copy()->addDays($this->days($settingKey)));
    }
}