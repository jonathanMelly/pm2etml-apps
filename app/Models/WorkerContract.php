<?php

namespace App\Models;

use App\Constants\RemediationStatus;
use App\Enums\CustomPivotTableNames;
use App\Enums\RequiredTimeUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use JetBrains\PhpStorm\Pure;
use Kirschbaum\PowerJoins\PowerJoins;

class WorkerContract extends Pivot
{

    protected $table = CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value;

    use PowerJoins;

    // Evaluation result constants
    const EVAL_NON_ACQUIS = 'na';
    const EVAL_PARTIELLEMENT_ACQUIS = 'pa';
    const EVAL_ACQUIS = 'a';
    const EVAL_LARGEMENT_ACQUIS = 'la';

    // Cannot use Enum... TODO Transform Enum to CONST !!!!

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('withoutTrashed', function (Builder $builder) {
            $builder->whereNull(tbl(WorkerContract::class).'.deleted_at');
        });

        // Handle cascade soft delete for evaluation attachments
        static::updated(function (WorkerContract $workerContract) {
            // If the worker contract is being soft deleted (deleted_at was set)
            if ($workerContract->isDirty('deleted_at') && $workerContract->deleted_at !== null) {
                // Soft delete all evaluation attachments using Eloquent to trigger events
                $workerContract->evaluationAttachments()->each(function ($attachment) {
                    $attachment->delete();
                });
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'success_date' => 'datetime',
            'allocated_time_unit' => RequiredTimeUnit::class,
        ];
    }

    public function contract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function groupMember(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupMember::class);
    }

    public function evaluationAttachments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ContractEvaluationAttachment::class, 'attachable');
    }

    /**
     * Evaluate the worker contract with a grade
     * @param string $evaluationResult - 'na', 'pa', 'a', or 'la'
     * @param string|null $comment
     * @param bool $save
     * @return bool
     */
    public function evaluate(string $evaluationResult, $comment = null, $save = true): bool
    {
        $this->evaluation_result = $evaluationResult;
        $this->success_date = now();
        $this->success_comment = $comment;

        if($this->remediation_status==RemediationStatus::CONFIRMED_BY_CLIENT)
        {
            $this->remediation_status = RemediationStatus::EVALUATED;
        }

        if ($save) {
            return $this->save();
        }

        return true;
    }

    /**
     * Check if the evaluation is a success (a or la)
     * @return bool
     */
    public function isSuccess(): bool
    {
        return in_array($this->evaluation_result, [self::EVAL_ACQUIS, self::EVAL_LARGEMENT_ACQUIS]);
    }

    public function alreadyEvaluated(): bool
    {
        return $this->evaluation_result !== null && !$this->hasPendingRemediation();
    }

    public function canRemediate():bool
    {
        return $this->alreadyEvaluated()
            && !$this->isSuccess()
            && $this->remediation_status === RemediationStatus::NONE;
    }

    public function hasPendingRemediation(): bool
    {
        return $this->remediation_status===RemediationStatus::CONFIRMED_BY_CLIENT;
    }

    public function getSuccessAsBoolString(): string
    {
        if (! $this->alreadyEvaluated()) {
            return 'n/a';
        }

        return $this->isSuccess() ? 'true' : 'false';
    }

    /**
     * Get human-readable evaluation result label
     * @return string
     */
    public function getEvaluationLabel(): string
    {
        return match($this->evaluation_result) {
            self::EVAL_LARGEMENT_ACQUIS => __('Largely Acquired'),
            self::EVAL_ACQUIS => __('Acquired'),
            self::EVAL_PARTIELLEMENT_ACQUIS => __('Partially Acquired'),
            self::EVAL_NON_ACQUIS => __('Not Acquired'),
            default => __('Not evaluated')
        };
    }

    #[Pure]
    public function getAllocatedTime(RequiredTimeUnit $targetUnit = RequiredTimeUnit::PERIOD): float
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
        } elseif ($allocatedTimeInPeriods < JobDefinition::SIZE_LARGE_MIN) {
            $size = 'Medium';
        } else {
            $size = 'Large';
        }

        return __($size).', ~'.$this->getAllocatedTime(RequiredTimeUnit::PERIOD).'p';
    }

    public function softDelete(): bool
    {
        if ($this->isDirty()) {
            throw new \Exception("Trying to softDelete a dirty workercontract with id {$this->id}. Please apply your modifications first to avoid any side effect...");
        } else {
            $this->deleted_at = now(); //soft delete not implemented on pivot

            return $this->save();
        }

    }
}
