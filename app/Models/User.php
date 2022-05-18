<?php

namespace App\Models;

use App\Constants\RoleName;
use App\Enums\CustomPivotTableNames;
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
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $username
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $last_logged_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Contract[] $contractsAsAClient
 * @property-read int|null $contracts_as_a_client_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupMember[] $groupMembers
 * @property-read int|null $group_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\JobDefinition[] $jobDefinitions
 * @property-read int|null $job_definitions_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastLoggedAt($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class User extends Model implements AuthenticatableContract,AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

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
        'last_logged_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [

    ];

    ///Overrides password with default as only used by o365
    //Added to avoid any issues with others modules deps (sessionHandling for instance...)
    public function getAuthPassword(): string
    {
        return "o365-external";
    }

    public function isAdmin():bool
    {
        return $this->hasRole(RoleName::ADMIN);
    }

    public function getInitials():string
    {
        return $this->firstname[0].$this->lastname[0];
    }
    public function getFirstnameL():string
    {
        return $this->firstname.' '.$this->lastname[0].'.';
    }

    /**
     * Tells of which jobDefinition this user is a provider
     * @return BelongsToMany
     */
    public function jobDefinitions(): BelongsToMany
    {
        return $this->belongsToMany(JobDefinition::class,CustomPivotTableNames::USER_JOB_DEFINITION->value);
    }

    public function groupMembers():HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     *
     * @return BelongsToMany
     */
    public function contractsAsAClient() : BelongsToMany
    {
        return $this->belongsToMany(Contract::class,CustomPivotTableNames::CONTRACT_USER->value);
    }

    //Filter and order contracts for the current client for a given job
    public function contractsAsAClientForJob(JobDefinition $job): Contract|Builder
    {
        $contract_client = CustomPivotTableNames::CONTRACT_USER->value;
        return Contract::
            //No power join as using the pivot table as a shortcut than the entire relation
            join($contract_client,tbl(Contract::class).'.id','=',$contract_client.'.contract_id')

            ->where('job_definition_id','=',$job->id)
            ->where($contract_client . '.user_id','=',$this->id)

            ->with('workers.user')
            ->with('workers.group.groupName')

            //Contract workers
            ->orderByPowerJoins('workers.group.groupName.year')
            ->orderByPowerJoins('workers.group.groupName.name')
            ->orderByPowerJoins('workers.user.lastname')
            ->orderByPowerJoins('workers.user.firstname');
    }

    /**
     *
     * @param null $periodId
     * @return BelongsToMany | Builder
     */
    public function contractsAsAWorker($periodId=null): BelongsToMany | Builder
    {
        $groupMember = $this->groupMember($periodId);
        if($groupMember===null)
        {
            return Contract::whereNull('id');//empty result
        }
        else
        {
            return $groupMember->workerContracts();
        }

    }

    public function groupMember($periodId=null): GroupMember | null
    {
        //get current period as default
        $periodId = $periodId??AcademicPeriod::current();

        // PERF COMMENT
        // After some basic tests, it’s not obvious if eloquent whereHas (extensively use SQL exist)
        // is less performant than a traditional inner join version... Thus the elegant way has been
        // choosen...

        /* @var $result GroupMember */
        $result = $this->groupMembers()
            ->whereHas('group.academicPeriod',fn($q)=>$q
                ->whereId($periodId))
            ->first();

        /*
        if($result===null)
        {
            //This may happen when looking for history when no data is available for the given period
            info('User with id='.$this->id.' has no entry in table '.tbl(GroupMember::class).' for the period with id='.$periodId);
            return GroupMember::make();
        }
        */

        return $result;

    }

    public function getJobDefinitionsWithActiveContracts($academicPeriodId): \Illuminate\Database\Eloquent\Collection
    {
        //TODO switch to polymorphic http://novate.co.uk/using-laravel-polymorphic-relationships-for-different-user-profiles/
        //so that only teacher has this method !
        if($this->hasRole(RoleName::TEACHER)===false)
        {
            throw new \Illuminate\Validation\UnauthorizedException('Only for teacher');
        }

        //TODO convert into powerRelation to avoid hard-coded table names and uses soft delete...
        $sqlQuery = "
                select jd.*,min(c.start) as min_start,max(c.end) as max_end,count(c.id) as contracts_count from job_definitions jd
                    inner join contracts c on c.job_definition_id=jd.id and c.deleted_at is null
                    inner join contract_client cc on cc.contract_id=c.id and cc.user_id=?

                    inner join contract_worker cw on cw.contract_id=c.id
                        inner join group_members gm on cw.group_member_id=gm.id and gm.deleted_at is null
                            inner join groups g on gm.group_id=g.id and g.deleted_at is null
                                inner join academic_periods ap on g.academic_period_id=ap.id and ap.id=? and ap.deleted_at is null

                    where jd.deleted_at is null

                    group by c.job_definition_id

                    order by min(c.`end`)
                ";

        return JobDefinition::fromQuery($sqlQuery,[$this->id,$academicPeriodId]);
    }

    /**
     * @param int $academicPeriodId
     * @return float the load percentage
     */
    public function getClientLoad(int $academicPeriodId) : array
    {
        $contractsForPeriodQuery = Contract::query()
            ->whereHas('workers.group.academicPeriod',fn($q)=>$q->where(tbl(AcademicPeriod::class).'.id','=',$academicPeriodId));
        $totalContractsForPeriod = $contractsForPeriodQuery->count('id');

        if($totalContractsForPeriod===0)
        {
            $percentage = 0;
            $currentUserContracts=0;
        }
        else
        {
            $currentUserContracts = $contractsForPeriodQuery
                ->whereHas('clients',fn($q)=>$q->where(tbl(User::class).'.id','=',$this->id))
                ->count('id');
            $percentage = round($currentUserContracts/$totalContractsForPeriod*100,0);
        }

        return [
            'percentage' => $percentage,
            'mine'=> $currentUserContracts,
            'total'=> $totalContractsForPeriod
        ];

    }

}
