<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperGradeWeight
 */
class GradeWeight extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['kkm' => 'float'];
    }
}