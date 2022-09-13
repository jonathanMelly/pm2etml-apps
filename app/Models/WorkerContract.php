<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WorkerContract extends Pivot
{
    // Cannot use Enum... TODO Transform Enum to CONST !!!!
    public $table='contract_worker';//\App\Enums\CustomPivotTableNames::CONTRACT_GROUP_MEMBER->value;

    public $casts = [
        'success_date' => 'datetime'
    ];

    public function contract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function groupMember(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupMember::class);
    }

    function evaluate($success,$comment=null,$save=true)
    {
        $this->success=$success;
        $this->success_date=now();
        $this->success_comment=$comment;

        if($save)
        {
            return $this->save();
        }
        return true;
    }

    function alreadyEvaluated():bool
    {
        return $this->success!==null;
    }

    function getSuccessAsBoolString()
    {
        if(!$this->alreadyEvaluated())
        {
            return 'n/a';
        }
        return $this->success?'true':'false';
    }
}
