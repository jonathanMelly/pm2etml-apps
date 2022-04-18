<?php

namespace App\Models;

use App\Enums\ContractRole;
use App\Enums\ContractStatus;
use App\Enums\CustomPivotTableNames;
use App\SwissFrenchDateFormat;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Contract
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property ContractStatus $status
 * @property string $status_timestamp
 * @property \Illuminate\Support\Carbon $start
 * @property \Illuminate\Support\Carbon $end
 * @property int $job_definition_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $clients
 * @property-read int|null $clients_count
 * @property-read \App\Models\JobDefinition|null $jobDefinition
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GroupMember[] $workers
 * @property-read int|null $workers_count
 * @method static \Database\Factories\ContractFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Query\Builder|Contract onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereJobDefinitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStatusTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Contract withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Contract withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Contract extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
        'status_timestamp',
        'start',
        'end'
    ];

    protected $casts=[
        'status' => ContractStatus::class,
        'start' => 'datetime',
        'end' => 'datetime',
    ];


    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class,CustomPivotTableNames::CONTRACT_USER->value)
            ->withTimestamps();
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(GroupMember::class,CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value)
            ->withTimestamps();
    }

    public function jobDefinition(): BelongsTo
    {
        return $this->belongsTo(JobDefinition::class);
    }
}
