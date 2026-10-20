<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(
        string $event,
        Model $model,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
        ?User $by = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $by?->id ?? Auth::id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
        ]);
    }
}