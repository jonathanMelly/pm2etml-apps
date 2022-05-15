<?php

namespace App\Models;

use App\Enums\ContractRole;
use App\Enums\CustomPivotTableNames;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JetBrains\PhpStorm\ArrayShape;
use Kirschbaum\PowerJoins\PowerJoins;

/**
 * App\Models\Contract
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $start
 * @property \Illuminate\Support\Carbon $end
 * @property \Illuminate\Support\Carbon|null $success_date last date of success field change, null=not evaluated
 * @property bool $success True if the work has been approved by the client
 * @property string|null $success_comment
 * @property int $job_definition_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $clients
 * @property-read int|null $clients_count
 * @property-read \App\Models\JobDefinition $jobDefinition
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupMember[] $workers
 * @property-read int|null $workers_count
 * @method static \Database\Factories\ContractFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract hasNestedUsingJoins($relations, $operator = '>=', $count = 1, $boolean = 'and', ?\Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract joinNestedRelationship(string $relationships, $callback = null, $joinType = 'join', $useAlias = false, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract joinRelation($relationName, $callback = null, $joinType = 'join', $useAlias = false, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract joinRelationship($relationName, $callback = null, $joinType = 'join', $useAlias = false, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract joinRelationshipUsingAlias($relationName, $callback = null, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract leftJoinRelation($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract leftJoinRelationship($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract leftJoinRelationshipUsingAlias($relationName, $callback = null, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Query\Builder|Contract onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByLeftPowerJoins($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByLeftPowerJoinsAvg($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByLeftPowerJoinsCount($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByLeftPowerJoinsMax($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByLeftPowerJoinsMin($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByLeftPowerJoinsSum($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByPowerJoins($sort, $direction = 'asc', $aggregation = null, $joinType = 'join')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByPowerJoinsAvg($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByPowerJoinsCount($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByPowerJoinsMax($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByPowerJoinsMin($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract orderByPowerJoinsSum($sort, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Contract powerJoinDoesntHave($relation, $boolean = 'and', ?\Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract powerJoinHas($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract powerJoinWhereHas($relation, $callback = null, $operator = '>=', $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract rightJoinRelation($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract rightJoinRelationship($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract rightJoinRelationshipUsingAlias($relationName, $callback = null, bool $disableExtraConditions = false)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereJobDefinitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSuccessComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSuccessDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Contract withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Contract withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Contract extends Model
{
    use HasFactory, SoftDeletes, PowerJoins;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'start',
        'end'
    ];

    protected $casts=[
        'start' => 'datetime',
        'end' => 'datetime',
        'success_date' => 'datetime'
    ];


    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class,CustomPivotTableNames::CONTRACT_USER->value)
            ->withTimestamps();
    }

    //Many workers = group project
    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(GroupMember::class,CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value)
            ->with('user')
            ->withTimestamps()
            ;
    }

    public function jobDefinition(): BelongsTo
    {
        return $this->belongsTo(JobDefinition::class);
    }

    #[ArrayShape(['percentage' => "float|int", 'remainingDays' => "int"])] public function getProgress(): array
    {
        $started  = $this->start <= today();
        $finished = $this->end < today();

        //1 day project
        if($this->start === $this->end)
        {
            $remainingHours = now()->diffInHours(now()->endOfDay()->toDateTime());
            $ratio = $remainingHours/24;
            $remainingDays = round($ratio,2);
            $progressPercentage = round($ratio * 100);
        }
        else if($finished)
        {
            $progressPercentage = 100;
            $remainingDays = 0;
        }
        //in progress
        else if($started)
        {
            $totalProgress = $this->start->diffInDays($this->end);
            $currentProgress = $this->start->diffInDays(now());
            $progressPercentage = round($currentProgress/$totalProgress * 100);
            $remainingDays = $totalProgress-$currentProgress;
        }
        //starts in the future
        else
        {
            $progressPercentage=0;
            $remainingDays = today()->diffInDays($this->end);
        }


        return ['percentage'=>$progressPercentage,'remainingDays'=>$remainingDays];
    }

    function evaluate($success,$comment=null,$save=true)
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
        return $this->success_date!==null;
    }

    function getSuccessAsBoolString()
    {
        return $this->success?'true':'false';
    }
}
