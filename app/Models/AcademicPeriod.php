<?php

namespace App\Models;

use App\SwissFrenchDateFormat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Période scolaire (année.
 *
 * .. ou si nécessaire, semestre, même trimestre...)
 */
class AcademicPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['start', 'end'];

    protected function casts(): array
    {
        return [
            'start' => 'immutable_date', 'end' => 'immutable_date',
        ];
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function groupNames(): HasManyThrough
    {
        return $this->hasManyThrough(GroupName::class, Group::class);
    }

    public static function current(bool $idOnly = true): AcademicPeriod|int|null
    {
        $key = 'currentAcademicPeriod'.($idOnly ? 'Id' : '');

        // Check if we have a cached value
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $today = today(); //don't try with DB:raw(now()) as it doesn't work on sqlite used for faster testing...
        $builder = self::forDate($today);

        if ($builder->exists()) {
            /* @var $period AcademicPeriod */
            $period = $builder->first();
            $result = $idOnly ? $period->id : $period;

            // Only cache when we found a period
            Cache::put($key, $result, Carbon::today()->secondsUntilEndOfDay());
            return $result;
        }

        // Don't cache when no period is found, just return the fallback value
        Log::warning('Missing period in db for '.today()->format(SwissFrenchDateFormat::DATE));
        return $idOnly ? -1 : null;
    }

    public static function forDate(Carbon $date): Builder
    {
        return (new static)::whereDate('start', '<=', $date)
            ->whereDate('end', '>=', $date);
    }

    public function printable(): string
    {
        return $this->start->year.'-'.$this->end->year;
    }

    public function __toString()
    {
        return '['.$this->start->toFormattedDateString().' => '.$this->end->toFormattedDateString().']';
    }
}
