<?php

namespace App\Models;

use App\Enums\CustomPivotTableNames;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['group_id', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function workerContracts(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value)
            ->using(WorkerContract::class);
    }
}
