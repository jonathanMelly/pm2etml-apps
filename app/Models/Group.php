<?php

namespace App\Models;

use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property int $group_name_id
 * @property int $academic_period_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\AcademicPeriod $academicPeriod
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupMember[] $groupMembers
 * @property-read int|null $group_members_count
 * @property-read \App\Models\GroupName $groupName
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Query\Builder|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereAcademicPeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereGroupNameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Group withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Group withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =['academic_period_id','group_name_id'];

    public function groupName():BelongsTo
    {
        return $this->belongsTo(GroupName::class);
    }

    public function academicPeriod():BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function groupMembers():HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function users():HasManyThrough
    {
        return $this->hasManyThrough(User::class,GroupMember::class);
    }
}
