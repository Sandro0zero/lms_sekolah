<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSemester
 */
class Semester extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['term' => 'integer', 'start_date' => 'date', 'end_date' => 'date', 'is_active' => 'boolean'];
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public function label(): string
    {
        return $this->academic_year . ' ' . ($this->term === 1 ? 'Ganjil' : 'Genap');
    }
}
