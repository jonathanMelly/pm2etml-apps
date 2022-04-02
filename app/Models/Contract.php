<?php

namespace App\Models;

use App\Enums\ContractRole;
use App\Enums\ContractStatus;
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
 * @property int $job_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $workers
 * @property-read int|null $workers_count
 * @method static \Database\Factories\ContractFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $status_timestamp
 * @property string $start_date
 * @property string $end_date
 * @property int $job_definition_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\JobDefinition|null $jobDefinition
 * @method static \Illuminate\Database\Query\Builder|Contract onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereJobDefinitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStatusTimestamp($value)
 * @method static \Illuminate\Database\Query\Builder|Contract withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Contract withoutTrashed()
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
        'start_date',
        'end_date'
    ];

    protected $casts=[
        'status' => ContractStatus::class
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivotValue('role',ContractRole::CLIENT->value)
            ->withTimestamps();
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivotValue('role',ContractRole::WORKER->value)
            ->withTimestamps();
    }

    public function jobDefinition(): BelongsTo
    {
        return $this->belongsTo(JobDefinition::class);
    }
}
