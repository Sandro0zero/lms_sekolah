<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAuditLog
 */
class AuditLog extends Model
{
    const UPDATED_AT = null; // tabel audit_logs hanya punya created_at

    protected $guarded = [];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}