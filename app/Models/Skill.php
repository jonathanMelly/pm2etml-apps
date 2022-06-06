<?php

namespace App\Models;

use App\Exceptions\BadFormatException;
use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kirschbaum\PowerJoins\PowerJoins;

/**
 * App\Models\Skill
 *
 * @property int $id
 * @property string $name
 * @property int $skill_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\JobDefinition[] $jobDefinitions
 * @property-read int|null $job_definitions_count
 * @property-read \App\Models\SkillGroup $skillGroup
 * @method static Builder|Skill hasNestedUsingJoins($relations, $operator = '>=', $count = 1, $boolean = 'and', ?\Closure $callback = null)
 * @method static Builder|Skill joinNestedRelationship(string $relationships, $callback = null, $joinType = 'join', $useAlias = false, bool $disableExtraConditions = false)
 * @method static Builder|Skill joinRelation($relationName, $callback = null, $joinType = 'join', $useAlias = false, bool $disableExtraConditions = false)
 * @method static Builder|Skill joinRelationship($relationName, $callback = null, $joinType = 'join', $useAlias = false, bool $disableExtraConditions = false)
 * @method static Builder|Skill joinRelationshipUsingAlias($relationName, $callback = null, bool $disableExtraConditions = false)
 * @method static Builder|Skill leftJoinRelation($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static Builder|Skill leftJoinRelationship($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static Builder|Skill leftJoinRelationshipUsingAlias($relationName, $callback = null, bool $disableExtraConditions = false)
 * @method static Builder|Skill newModelQuery()
 * @method static Builder|Skill newQuery()
 * @method static \Illuminate\Database\Query\Builder|Skill onlyTrashed()
 * @method static Builder|Skill orderByLeftPowerJoins($sort, $direction = 'asc')
 * @method static Builder|Skill orderByLeftPowerJoinsAvg($sort, $direction = 'asc')
 * @method static Builder|Skill orderByLeftPowerJoinsCount($sort, $direction = 'asc')
 * @method static Builder|Skill orderByLeftPowerJoinsMax($sort, $direction = 'asc')
 * @method static Builder|Skill orderByLeftPowerJoinsMin($sort, $direction = 'asc')
 * @method static Builder|Skill orderByLeftPowerJoinsSum($sort, $direction = 'asc')
 * @method static Builder|Skill orderByPowerJoins($sort, $direction = 'asc', $aggregation = null, $joinType = 'join')
 * @method static Builder|Skill orderByPowerJoinsAvg($sort, $direction = 'asc')
 * @method static Builder|Skill orderByPowerJoinsCount($sort, $direction = 'asc')
 * @method static Builder|Skill orderByPowerJoinsMax($sort, $direction = 'asc')
 * @method static Builder|Skill orderByPowerJoinsMin($sort, $direction = 'asc')
 * @method static Builder|Skill orderByPowerJoinsSum($sort, $direction = 'asc')
 * @method static Builder|Skill powerJoinDoesntHave($relation, $boolean = 'and', ?\Closure $callback = null)
 * @method static Builder|Skill powerJoinHas($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
 * @method static Builder|Skill powerJoinWhereHas($relation, $callback = null, $operator = '>=', $count = 1)
 * @method static Builder|Skill query()
 * @method static Builder|Skill rightJoinRelation($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static Builder|Skill rightJoinRelationship($relation, $callback = null, $useAlias = false, bool $disableExtraConditions = false)
 * @method static Builder|Skill rightJoinRelationshipUsingAlias($relationName, $callback = null, bool $disableExtraConditions = false)
 * @method static Builder|Skill whereCreatedAt($value)
 * @method static Builder|Skill whereDeletedAt($value)
 * @method static Builder|Skill whereId($value)
 * @method static Builder|Skill whereName($value)
 * @method static Builder|Skill whereSkillGroupId($value)
 * @method static Builder|Skill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Skill withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Skill withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Skill extends Model
{
    use SoftDeletes, PowerJoins;

    public const SEPARATOR = ':';

    protected $fillable = ['name','skill_group_id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('orderByName', function (Builder $builder) {
            $builder
                ->orderByPowerJoins('skillGroup.name')
                ->orderBy('name', 'asc');
        });
    }

    public function skillGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SkillGroup::class);
    }

    public function jobDefinitions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(JobDefinition::class);
    }

    public static function firstOrCreateFromString(string $nameAndGroup,
                                                   string $separator=self::SEPARATOR): Model|Skill
    {
        if(!preg_match('/.+:.+/',$nameAndGroup))
        {
            throw new BadFormatException(__('Wrong skill format, expected ...:...'));
        }
        $parts = explode($separator,$nameAndGroup);
        return self::firstOrCreateWithGroup($parts[0],$parts[1],$separator);

    }

    public static function firstOrCreateWithGroup(string $skillGroup,
                                                  string $skillName,
                                                  string $separator=self::SEPARATOR): Model|Skill
    {
        $skillGroup = SkillGroup::firstOrCreate(['name'=>trim($skillGroup)]);
        return Skill::withoutGlobalScope('orderByName')
            ->firstOrCreate([
            'name'=>trim($skillName),
            'skill_group_id'=>$skillGroup->id
        ]);
    }

    public function getFullName(): string
    {
        return $this->skillGroup->name.self::SEPARATOR.' '.$this->name;
    }
}
