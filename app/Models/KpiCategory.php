<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperKpiCategory
 */
class KpiCategory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['min_score' => 'float', 'max_score' => 'float'];
    }
}