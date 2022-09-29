<?php

namespace App\Models;

use App\Enums\ContractRole;
use App\Enums\CustomPivotTableNames;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JetBrains\PhpStorm\ArrayShape;
use Kirschbaum\PowerJoins\PowerJoins;

class Contract extends Model
{
    /**
     * WARNING | ATTENTION : même si un contrat peut avoir plusieurs clients / employés
     * Actuellement, l’évaluation est faite au niveau du contrat (même éval pour tous les employés)!!!
     * Pour changer cela, il faudrait mettre l’évaluation dans la table CONTRACT_WORKER
     * voir https://github.com/jonathanMelly/pm2etml-intranet/issues/40
     */

    use HasFactory, SoftDeletes, PowerJoins;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'start',
        'end'
    ];

    protected $casts=[
        'start' => 'datetime',
        'end' => 'datetime',
    ];


    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class,CustomPivotTableNames::CONTRACT_USER->value)
            ->withTimestamps();
    }

    //Many workers = group project
    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(GroupMember::class,CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value)
            ->with('user')
            ->withTimestamps()
            ->using(WorkerContract::class)
            ;
    }

    public function workersContracts(): HasMany
    {
        return $this->HasMany(WorkerContract::class);
    }

    public function getWorkerContract($userid)
    {
        return $this->workers->where('user_id','=',$userid)->firstOrFail()->pivot;
    }

    public function jobDefinition(): BelongsTo
    {
        return $this->belongsTo(JobDefinition::class);
    }

    #[ArrayShape(['percentage' => "float|int", 'remainingDays' => "int"])] public function getProgress(): array
    {
        $started  = $this->start <= today();
        $finished = $this->end < today();

        //1 day project
        if($this->start === $this->end)
        {
            $remainingHours = now()->diffInHours(now()->endOfDay()->toDateTime());
            $ratio = $remainingHours/24;
            $remainingDays = round($ratio,2);
            $progressPercentage = round($ratio * 100);
        }
        else if($finished)
        {
            $progressPercentage = 100;
            $remainingDays = 0;
        }
        //in progress
        else if($started)
        {
            $totalProgress = $this->start->diffInDays($this->end);
            $currentProgress = $this->start->diffInDays(now());
            $progressPercentage = round($currentProgress/$totalProgress * 100);
            $remainingDays = $totalProgress-$currentProgress;
        }
        //starts in the future
        else
        {
            $progressPercentage=0;
            $remainingDays = today()->diffInDays($this->end);
        }


        return ['percentage'=>$progressPercentage,'remainingDays'=>$remainingDays];
    }

}
