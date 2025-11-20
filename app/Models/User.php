<?php

namespace App\Models;

use App\Constants\RoleName;
use App\Enums\CustomPivotTableNames;
use App\Exceptions\DataIntegrityException;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'username',
        'last_logged_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    ///Overrides password with default as only used by o365
    //Added to avoid any issues with others modules deps (sessionHandling for instance...)
    public function getAuthPassword(): string
    {
        return 'o365-external';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleName::ADMIN);
    }

    public function getInitials(): string
    {
        return $this->firstname[0] . $this->lastname[0];
    }

    public function getFirstnameL(bool $withId = false): string
    {
        return $this->getFirstnameLX(2,$withId);
    }

    public function getFirstnameLX($x = 0, bool $withId = false): string
    {
        return $this->firstname . ' ' . mb_substr($this->lastname, 0, $x) . '.' . ($withId ? "<{$this->id}>" : '');
    }

    //Not perfect (do not handle clashes but avoids adding this data for now)
    public function getAcronym(): string
    {
        return mb_strtoupper(mb_substr($this->firstname, 0, 1) . mb_substr($this->lastname, 0, 1) . mb_substr($this->lastname, -1, 1));
    }

    /**
     * Tells of which jobDefinition this user is a provider
     */
    public function jobDefinitions(): BelongsToMany
    {
        return $this->belongsToMany(JobDefinition::class, CustomPivotTableNames::USER_JOB_DEFINITION->value)
            ->withTimestamps();
    }

    public function groupMembers(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function contractsAsAClient(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, CustomPivotTableNames::CONTRACT_USER->value)
            ->withTimestamps();
    }

    /**
     * Returns true if there are contracts for which users have expressed a wish (and not a firm commitment)
     */
    public function hasPendingContractApplications(): bool
    {
        return WorkerContract::whereHas('contract', function ($query) {
            $query->whereHas('clients', function ($query) {
                $query->where('user_id', $this->id);
            });
        })->where('application_status', '>', 0)->exists();
    }

    public function involvedGroupNames(int $periodId): Collection
    {
        return Cache::rememberForever(
            "involvedGroupNames-$this->id",
            function () use ($periodId) {
                return GroupName::query()
                    ->distinct()
                    ->select('name')
                    ->whereHas(
                        'groups.groupMembers',
                        fn($q) => $q->whereIn(
                            'id',

                            WorkerContract::query()
                                ->whereHas('groupMember.group.academicPeriod', fn($q) => $q->where(tbl(AcademicPeriod::class) . '.id', '=', $periodId))
                                ->whereHas('contract.clients', fn($q) => $q->where(tbl(User::class) . '.id', '=', $this->id))
                                ->pluck('group_member_id')
                        )

                    )
                    ->pluck('name');
            }
        );
    }

    //Filter and order contracts for the current client for a given job
    public function contractsAsAClientForJob(JobDefinition $job, int $periodId): Contract|Builder
    {
        $contract_client = CustomPivotTableNames::CONTRACT_USER->value;

        return Contract::
            //No power join as using the pivot table as a shortcut than the entire relation
            join($contract_client, tbl(Contract::class) . '.id', '=', $contract_client . '.contract_id')
            ->where('job_definition_id', '=', $job->id)
            ->where($contract_client . '.user_id', '=', $this->id)
            ->whereHas('workers.group.academicPeriod', fn($q) => $q->where(tbl(AcademicPeriod::class) . '.id', '=', $periodId))
            ->with('workers.user')
            ->with('workers.group.groupName')
            ->with('workersContracts.evaluationAttachments')

            //Contract workers
            ->orderByPowerJoins('workers.group.groupName.year')
            ->orderByPowerJoins('workers.group.groupName.name')
            ->orderByPowerJoins('workers.user.lastname')
            ->orderByPowerJoins('workers.user.firstname');
    }

    public function pastContractsAsAWorker(?int $currentPeriodId = null): Contract|Builder
    {
       return Contract::whereRelation('workers.user','id','=',$this->id)
            ->whereRelation('workers.group.academicPeriod','id','<',$currentPeriodId)
           ->with('jobDefinition') //eager load definitions as needed on UI
           ->with('clients') //eager load clients as needed on UI
           ->with('workersContracts.evaluationAttachments') //eager load evaluation attachments
           ->orderByDesc('end')
           ->orderByDesc('start');
    }

    public function contractsAsAWorker(?int $periodId = null): BelongsToMany|Contract|Builder
    {
        $groupMember = $this->groupMember($periodId);
        if ($groupMember === null) {
            //This should never happen !(any student must have a groupMember for the platform to work...)
            if ($this->hasRole(RoleName::STUDENT)) {
                Log::warning("Missing groupmember for user with id: $this->id and periodId $periodId");
            }
            //WARNING, this tricks makes the jobdef scan (jobdefcontroller->marketplace) work in any state...
            //Do not modify this unless you know what you do
            return Contract::whereNull('id'); //empty result
        } else {
            return $groupMember->workerContracts();
        }
    }

    public function joinGroup(?int $periodId, string $groupName, ?int $year = null): GroupMember
    {
        $periodId = $periodId ?? AcademicPeriod::current();

        //In case of a user switching back to a previously associated-then-dissociated group, we just restore it ! (as db won’t allow us to create another...)

        /* @var $trashedJoin GroupMember */
        $trashedJoin = GroupMember::withTrashed()
            ->where('user_id', '=', $this->id)
            ->whereHas('group.academicPeriod', fn($q) => $q->whereId($periodId))
            ->whereHas('group.groupName', fn($q) => $q->where('name', '=', $groupName))->first();

        if ($trashedJoin !== null) {
            if ($trashedJoin->restore()) {
                return $trashedJoin;
            } else {
                throw new DataIntegrityException('Cannot restore GroupMember for ' . $this->email . ' / ' . $groupName . ' for period ' . $periodId);
            }
        }

        return $this->groupMembers()->create(
            [
                'user_id' => $this->id,
                'group_id' => \App\Models\Group::firstOrCreate([
                    'academic_period_id' => $periodId,
                    'group_name_id' => \App\Models\GroupName::firstOrCreate(['name' => $groupName, 'year' => $year ?? GroupName::guessGroupNameYear($groupName)])->id,
                ])->id,
            ]
        );
    }

    public function groupMembersForPeriod(?int $periodId = null): HasMany
    {
        // PERF COMMENT
        // After some basic tests, it’s not obvious if eloquent whereHas (extensively use SQL exist)
        // is less performant than a traditional inner join version... Thus the elegant way has been
        // choosen...

        $hasMany = $this->groupMembers();
        if ($periodId !== null) {
            $hasMany->whereHas('group.academicPeriod', fn($q) => $q->whereId($periodId));
        }

        return $hasMany;
    }

    public function groupMember($periodId = null, $withGroupName = false): ?GroupMember
    {
        //get current period as default
        $periodId = $periodId ?? AcademicPeriod::current();

        $builder = $this->groupMembersForPeriod($periodId);
        if ($withGroupName) {
            $builder->with('group.groupName');
        }

        return $builder->first();
    }

    //REMEMBER that GroupMember is mapped as a standard MODEL to be able to use softdelete on it...
    //So we don’t use belongsToMany or hasManyThrough...
    public function groups(int|AcademicPeriod|null $periodId = null): Builder
    {
        $query = Group::query()->whereHas('groupMembers', fn($q) => $q->where('user_id', '=', $this->id));
        if ($periodId !== null) {
            if ($periodId instanceof AcademicPeriod) {
                $periodId = $periodId->id;
            }
            $query->where('academic_period_id', '=', $periodId);
        }

        return $query;
    }

    //A teacher can have multiple groupNames for a given period...
    //Students should’nt have
    public function groupNames(int|AcademicPeriod|null $periodId = null): Builder
    {
        return GroupName::distinct()->select('name')
            ->whereIn('id', $this->groups($periodId)->pluck('group_name_id'));
    }

    public function getGroupNames($periodId = null, $printable = false): Collection|string
    {
        $names = $this->groupNames($periodId)->pluck('name');
        if ($printable) {
            return $names->transform(fn($el) => strtoupper($el))->implode(',');
        }

        return $names;
    }

    public function getJobDefinitions(int $academicPeriodId): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->hasRole(RoleName::TEACHER) === false) {
            throw new \Illuminate\Validation\UnauthorizedException('Only for teacher');
        }
        return JobDefinition::powerJoinWhereHas('providers',fn($q)=>$q->where('id','=',$this->id))->get();
    }

    public function getJobDefinitionsWithActiveContracts(int $academicPeriodId): \Illuminate\Database\Eloquent\Collection
    {
        //TODO switch to polymorphic http://novate.co.uk/using-laravel-polymorphic-relationships-for-different-user-profiles/
        //OR https://github.com/calebporzio/parental
        //so that only teacher has this method !
        if ($this->hasRole(RoleName::TEACHER) === false) {
            throw new \Illuminate\Validation\UnauthorizedException('Only for teacher');
        }

        //When a job is trashed, some contracts may still be pending... Let contracts be deleted separately
        $whereJob = ''; //"where jd.deleted_at is null";

        //TODO convert into powerRelation to avoid hard-coded table names and uses soft delete...
        $sqlQuery = "
                select jd.*,min(c.start) as min_start,max(c.end) as max_end,count(c.id) as contracts_count from job_definitions jd
                    inner join contracts c on c.job_definition_id=jd.id and c.deleted_at is null
                    inner join contract_client cc on cc.contract_id=c.id and cc.user_id=?

                    inner join contract_worker cw on cw.contract_id=c.id and cw.deleted_at is null
                        inner join group_members gm on cw.group_member_id=gm.id and gm.deleted_at is null
                            inner join groups g on gm.group_id=g.id and g.deleted_at is null
                                inner join academic_periods ap on g.academic_period_id=ap.id and ap.id=? and ap.deleted_at is null

                    $whereJob

                    group by c.job_definition_id

                    order by min(c.`end`)
                ";

        return JobDefinition::fromQuery($sqlQuery, [$this->id, $academicPeriodId]);
    }

    /**
     * To avoid perf issues, cache is used and flushed in controllers when modification is done
     * As hooking events on pivot tables needs add model for them, care must be taken to avoid
     * bad cache data (always flush cache on contract client updates...)
     *
     * @return float the load percentage
     */
    public function getClientLoad(int $academicPeriodId): array
    {
        return Cache::rememberForever('client-' . $this->id . '-percentage', function () use ($academicPeriodId) {
            $contractsForPeriodQuery = Contract::query()
                ->whereHas('workers.group.academicPeriod', fn($q) => $q->where(tbl(AcademicPeriod::class) . '.id', '=', $academicPeriodId));
            $totalContractsForPeriod = $contractsForPeriodQuery->count('id');

            if ($totalContractsForPeriod === 0) {
                $percentage = 0;
                $currentUserContracts = 0;
            } else {
                $currentUserContracts = $contractsForPeriodQuery
                    ->whereHas('clients', fn($q) => $q->where(tbl(User::class) . '.id', '=', $this->id))
                    ->count('id');
                $percentage = round($currentUserContracts / $totalContractsForPeriod * 100, 0);
            }

            return [
                'percentage' => $percentage,
                'mine' => $currentUserContracts,
                'total' => $totalContractsForPeriod,
            ];
        });
    }
}
