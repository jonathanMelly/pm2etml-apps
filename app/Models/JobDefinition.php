<?php

namespace App\Models;

use App\Constants\RoleName;
use App\Enums\CustomPivotTableNames;
use App\Enums\JobPriority;
use App\Enums\RequiredTimeUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\Pure;

class JobDefinition extends Model
{
    use HasFactory, SoftDeletes;

    public const MIN_PERIODS = 24;

    public const MAX_PERIODS = 999;

    public const MIN_WISH_PRIORITY = 1;

    public const MAX_WISH_PRIORITY = 3;

    const SIZE_MEDIUM_MIN = 90;

    const SIZE_LARGE_MIN = 120;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'required_xp_years',
        'priority',
        'status',
        'published_date',
        'image_attachment_id',
        'allocated_time',
        'one_shot',
        'by_application',
    ];

    protected function casts(): array
    {
        return [
            'priority' => JobPriority::class,
            'allocated_time_unit' => RequiredTimeUnit::class,
            'published_date' => 'datetime',
        ];
    }

    public function scopeFilter(Builder $query, Request $request)
    {
        //WARNING: trashed filter (archived) must be handled first
        //as withTrashed must be applied before any where condition...
        $onlyPublished = true;
        //Students should not see drafts
        if (! $request->user()->hasRole(RoleName::STUDENT)) {
            if (($input = existsAndNotEmpty($request, 'status')) != null) {
                if ($input === 'only') {
                    $query->where(function (Builder $q) {
                        $q
                            ->where('published_date', '>', now())
                            ->orWhereNull('published_date');
                    });
                    $onlyPublished = false;
                } elseif ($input === 'include') {
                    $onlyPublished = false;
                }
                elseif($input === 'trashed'){
                    $query->withTrashed();
                    $onlyPublished=false;
                }
            }
        }
        if ($onlyPublished) {
            $query->where(fn($q) => $q->published());
        }
        
        //Simple ones
        foreach (['required_xp_years', 'priority'] as $filter) {
            if (($input = existsAndNotEmpty($request, $filter)) != null) {
                $query->where($filter, '=', $input);
            }
        }

        // Sizes
        if (($input = existsAndNotEmpty($request, 'size')) != null) {
            //TODO Handle hours/periods...
            $min = match ($input) {
                default => 0,
                'md' => self::SIZE_MEDIUM_MIN,
                'lg' => self::SIZE_LARGE_MIN
            };
            $max = match ($input) {
                'sm' => self::SIZE_MEDIUM_MIN - 1,
                'md' => self::SIZE_LARGE_MIN - 1,
                default => self::MAX_PERIODS
            };
            $query->whereBetween('allocated_time', [$min, $max]);
        }

        if (($input = existsAndNotEmpty($request, 'provider')) != null) {
            $query->whereHas('providers', fn($q) => $q->where(tbl(User::class) . '.id', '=', $input));
        }

        if (($input = existsAndNotEmpty($request, 'fulltext')) != null) {
            $query->where(function (Builder $q) use ($input) {
                $q->where('title', 'LIKE', '%' . $input . '%');
                $q->orWhere('description', 'LIKE', '%' . $input . '%');
                $q->orWhereHas('providers', function ($q) use ($input) {
                    $q->where(tbl(User::class) . '.firstname', 'LIKE', '%' . $input . '%');
                    $q->orWhere(tbl(User::class) . '.lastname', 'LIKE', '%' . $input . '%');
                });
            });
        }


        return $query;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published_date', '<=', now());
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('one_shot', '=', false)
            ->orWhereDoesntHave('contracts');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
        //->with('workers.group.groupName',fn($q)=>$q->orderBy('name'))
        //->with('workers.user',fn($q)=>$q->orderBy('lastname')->orderBy('firstname'));
    }

    /**
     * Returns the pending application (i.e: application_status > 0) of the user for this job ...
     * ... or null if he has not applied or is confirmed
     */
    public function pendingApplicationFrom(User $user): ?WorkerContract
    {
        return WorkerContract::whereHas('groupMember', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereHas('contract', function ($q) {
                $q->where('job_definition_id', $this->id);
            })
            ->where('application_status', '>', 0)
            ->first();
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, CustomPivotTableNames::USER_JOB_DEFINITION->value)
            /*->as("providers")*/
            ->withTimestamps();
    }

    public function attachments(): MorphMany
    {
        $relation = $this->morphMany(JobDefinitionDocAttachment::class, 'attachable');
        if ($this->trashed()) {
            return $relation->withTrashed();
        }

        return $relation;
    }

    public function image(): MorphOne
    {

        $relation = $this->morphOne(
            related: JobDefinitionMainImageAttachment::class,
            name: 'attachable'
        );

        if ($this->trashed()) {
            return $relation->withTrashed();
        }

        return $relation;
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
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
        if ($allocatedTimeInPeriods < self::SIZE_MEDIUM_MIN) {
            $size = 'Weak';
        } elseif ($allocatedTimeInPeriods < self::SIZE_LARGE_MIN) {
            $size = 'Medium';
        } else {
            $size = 'Large';
        }

        return __($size) . ', ~' . $this->getAllocatedTime(RequiredTimeUnit::PERIOD) . 'p';
    }

    public function isPublished(): bool
    {
        return $this->published_date !== null && $this->published_date->isBefore(Carbon::tomorrow());
    }

    public function sortUsers(Collection $users, bool $byLoad = true): Collection
    {
        $orders = [];

        if ($byLoad) {
            $orders[] = fn($a, $b) => $a->getClientLoad(\App\Models\AcademicPeriod::current())['percentage'] <=>
                $b->getClientLoad(\App\Models\AcademicPeriod::current())['percentage'];
        }
        $orders[] = fn($a, $b) => $a['lastname'] <=> $b['lastname'];

        return $users->sortBy($orders)->values();
    }

    public function getProviders(bool $sortedByLoad = true): Collection
    {
        return $this->sortUsers($this->providers, $sortedByLoad);
    }

    public function getClients(Collection $exclude, bool $sortedByLoad = true): Collection
    {
        $clients = User::role(RoleName::TEACHER)->get()->filter(fn($el) => $exclude->doesntContain('id', '=', $el->id));

        return $this->sortUsers($clients, $sortedByLoad);
    }

    public function delete()
    {
        return DB::transaction(function () {
            //Mark attachments as deleted (do not use mass delete to keep EVENT processing)
            Attachment::where('attachable_id', '=', $this->id)
                ->each(fn($a) => $a->delete());

            return parent::delete();
        });
    }
}
