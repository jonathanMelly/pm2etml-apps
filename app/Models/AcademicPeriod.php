<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * Période scolaire (année.
 *
 * .. ou si nécessaire, semestre, même trimestre...)
 *
 * @property int $id
 * @property \Carbon\CarbonImmutable $start
 * @property \Carbon\CarbonImmutable $end
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupName[] $groupNames
 * @property-read int|null $group_names_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @method static Builder|AcademicPeriod newModelQuery()
 * @method static Builder|AcademicPeriod newQuery()
 * @method static \Illuminate\Database\Query\Builder|AcademicPeriod onlyTrashed()
 * @method static Builder|AcademicPeriod query()
 * @method static Builder|AcademicPeriod whereCreatedAt($value)
 * @method static Builder|AcademicPeriod whereDeletedAt($value)
 * @method static Builder|AcademicPeriod whereEnd($value)
 * @method static Builder|AcademicPeriod whereId($value)
 * @method static Builder|AcademicPeriod whereStart($value)
 * @method static Builder|AcademicPeriod whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|AcademicPeriod withTrashed()
 * @method static \Illuminate\Database\Query\Builder|AcademicPeriod withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class AcademicPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = ['start'=>'immutable_date','end'=>'immutable_date'];
    protected $fillable = ['start','end'];

    public function groups():HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function groupNames():HasManyThrough
    {
        return $this->hasManyThrough(GroupName::class,Group::class);
    }

    public static function current(bool $idOnly=true): AcademicPeriod|int
    {
        $key = 'currentAcademicPeriod'.($idOnly?'Id':'');
        return Cache::remember($key,Carbon::today()->secondsUntilEndOfDay(), function () use($idOnly) {
            $today = today(); //don’t try with DB:raw(now()) as it doesn’t work on sqlite used for faster testing...
            $builder = self::forDate($today);

            if($idOnly)
            {
                return $builder->firstOrFail(['id'])['id'];
            }

            return $builder->firstOrFail();
        });

    }

    public static function forDate(Carbon $date): Builder
    {
        return (new static)::whereDate('start','<=',$date)
            ->whereDate('end','>=',$date);
    }

    public function __toString()
    {
        return '['.$this->start->toFormattedDateString().' => '.$this->end->toFormattedDateString().']';
    }

}
