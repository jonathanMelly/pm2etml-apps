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
        return $this->belongsToMany(JobDefinition::class);
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
            return Contract::whereNull('id');
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

}
