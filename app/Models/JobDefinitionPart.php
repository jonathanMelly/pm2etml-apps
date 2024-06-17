<?php

namespace App\Models;

use App\Enums\RequiredTimeUnit;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

class JobDefinitionPart extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_definition_id',
        'name',
        'allocated_time',
        'allocated_time_unit',
    ];

    protected function casts(): array
    {
        return [
            'allocated_time_unit' => RequiredTimeUnit::class,
        ];
    }

    #[Pure]
    public function getAllocatedTime(RequiredTimeUnit $targetUnit = RequiredTimeUnit::PERIOD): float
    {
        if ($this->allocated_time === null) {
            return 0;
        }

        return round(RequiredTimeUnit::Convert($this->allocated_time, $this->allocated_time_unit, $targetUnit), 0);
    }
}
