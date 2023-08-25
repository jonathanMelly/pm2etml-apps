<?php

namespace App\Models;

use App\Enums\RequiredTimeUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use JetBrains\PhpStorm\Pure;
use Kirschbaum\PowerJoins\PowerJoins;
use phpDocumentor\Reflection\Types\This;

class WorkerContract extends Pivot
{

    use PowerJoins;

    // Cannot use Enum... TODO Transform Enum to CONST !!!!
    public $table='contract_worker';//\App\Enums\CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value;

    public $casts = [
        'deleted_at'=>'datetime',
        'success_date' => 'datetime',
        'allocated_time_unit' => RequiredTimeUnit::class
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('withoutTrashed', function (Builder $builder) {
            $builder->whereNull(tbl(WorkerContract::class). '.deleted_at');
        });
    }

    public function contract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function groupMember(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupMember::class);
    }

    function evaluate($success,$comment=null,$save=true): bool
    {
        $this->success=$success;
        $this->success_date=now();
        $this->success_comment=$comment;

        if($save)
        {
            return $this->save();
        }
        return true;
    }

    function alreadyEvaluated():bool
    {
        return $this->success!==null;
    }

    function getSuccessAsBoolString(): string
    {
        if(!$this->alreadyEvaluated())
        {
            return 'n/a';
        }
        return $this->success?'true':'false';
    }

    #[Pure] public function getAllocatedTime(RequiredTimeUnit $targetUnit = RequiredTimeUnit::PERIOD): float
    {
        if ($this->allocated_time === null) {
            return 0;
        }
        return round(RequiredTimeUnit::Convert($this->allocated_time, $this->allocated_time_unit, $targetUnit), 0);
    }

    public function getAllocationDetails(): string
    {

        $allocatedTimeInPeriods = $this->getAllocatedTime(RequiredTimeUnit::PERIOD);
        if ($allocatedTimeInPeriods < JobDefinition::SIZE_MEDIUM_MIN) {
            $size = 'Weak';
        } else if ($allocatedTimeInPeriods < JobDefinition::SIZE_LARGE_MIN) {
            $size = 'Medium';
        } else {
            $size = 'Large';
        }

        return __($size) . ', ~' . $this->getAllocatedTime(RequiredTimeUnit::PERIOD) . 'p';
    }
}
