<?php

namespace App\Models;

use App\Constants\RemediationStatus;
use App\Enums\CustomPivotTableNames;
use App\Enums\RequiredTimeUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use JetBrains\PhpStorm\Pure;
use Kirschbaum\PowerJoins\PowerJoins;

class WorkerContract extends Pivot
{
    use PowerJoins;

    protected $table = CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value;

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('withoutTrashed', function (Builder $builder) {
            $builder->whereNull(tbl(WorkerContract::class) . '.deleted_at');
        });
    }

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'success_date' => 'datetime',
            'allocated_time_unit' => RequiredTimeUnit::class,
        ];
    }

    /**
     * Le contrat principal auquel ce WorkerContract est lié.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Le lien vers le membre du groupe (relie user et group).
     */
    public function groupMember(): BelongsTo
    {
        return $this->belongsTo(GroupMember::class, 'group_member_id');
    }

    /**
     * L'utilisateur (élève) relié via GroupMember.
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            GroupMember::class,
            'id',          // clé locale dans GroupMember
            'id',          // clé locale dans User
            'group_member_id', // clé étrangère dans WorkerContract
            'user_id'      // clé étrangère dans GroupMember
        );
    }

    /**
     * Le groupe auquel appartient ce WorkerContract.
     */
    public function group(): HasOneThrough
    {
        return $this->hasOneThrough(
            Group::class,
            GroupMember::class,
            'id',
            'id',
            'group_member_id',
            'group_id'
        );
    }

    /**
     * Le nom de la classe (groupName) via Group -> GroupName.
     */
    public function groupName(): HasOneThrough
    {
        return $this->hasOneThrough(
            GroupName::class,
            Group::class,
            'id',
            'id',
            'group_id',
            'group_name_id'
        );
    }

    /**
     * L'évaluation associée à ce WorkerContract.
     */
    public function workerContractAssessment(): HasOne
    {
        return $this->hasOne(WorkerContractAssessment::class, 'worker_contract_id');
    }


    public function teacherFromAssessment()
    {
        return $this->workerContractAssessment?->teacher;
    }


    /**
     * Évaluer ce WorkerContract.
     */
    public function evaluate(?bool $success, $comment = null, $save = true): bool
    {
        $this->success = $success;
        $this->success_date = now();
        $this->success_comment = $comment;

        if ($this->remediation_status == RemediationStatus::CONFIRMED_BY_CLIENT) {
            $this->remediation_status = RemediationStatus::EVALUATED;
        }

        if ($save) {
            return $this->save();
        }

        return true;
    }

    /**
     * Vérifie si le contrat a déjà été évalué.
     */
    public function alreadyEvaluated(): bool
    {
        return $this->success !== null && !$this->hasPendingRemediation();
    }

    /**
     * Vérifie si une remédiation est possible.
     */
    public function canRemediate(): bool
    {
        return $this->alreadyEvaluated()
            && $this->success == false
            && $this->remediation_status === RemediationStatus::NONE;
    }

    /**
     * Vérifie s’il existe une remédiation en attente.
     */
    public function hasPendingRemediation(): bool
    {
        return $this->remediation_status === RemediationStatus::CONFIRMED_BY_CLIENT;
    }

    /**
     * Convertit le temps alloué dans une unité donnée.
     */
    #[Pure]
    public function getAllocatedTime(RequiredTimeUnit $targetUnit = RequiredTimeUnit::PERIOD): float
    {
        if ($this->allocated_time === null) {
            return 0;
        }

        return round(RequiredTimeUnit::Convert($this->allocated_time, $this->allocated_time_unit, $targetUnit), 0);
    }

    /**
     * Retourne une description textuelle du temps alloué.
     */
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

        return __($size) . ', ~' . $this->getAllocatedTime(RequiredTimeUnit::PERIOD) . 'p';
    }

    /**
     * Supprime logiquement le contrat.
     */
    public function softDelete(): bool
    {
        if ($this->isDirty()) {
            throw new \Exception("Trying to softDelete a dirty WorkerContract with id {$this->id}. Please apply your modifications first to avoid any side effect...");
        } else {
            $this->deleted_at = now();
            return $this->save();
        }
    }
}
