<?php

namespace App\Models;

use App\Enums\CustomPivotTableNames;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Pest\Laravel\be;
use function Symfony\Component\String\b;

/**
 * App\Models\GroupMember
 *
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property int $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Contract[] $workerContracts
 * @property-read int|null $worker_contracts_count
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember newQuery()
 * @method static \Illuminate\Database\Query\Builder|GroupMember onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GroupMember whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|GroupMember withTrashed()
 * @method static \Illuminate\Database\Query\Builder|GroupMember withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class GroupMember extends Model
{
    use HasFactory, SoftDeletes;

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group():BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function workerContracts():BelongsToMany
    {
        return $this->belongsToMany(Contract::class,CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value);
    }
}
