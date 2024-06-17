<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerContractEvaluationLog extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $casts = [
        'old_start' => 'datetime',
        'new_start' => 'datetime',
        'old_end' => 'datetime',
        'new_end' => 'datetime',
        'old_date' => 'datetime',
        'new_date' => 'datetime',
    ];

    public function contract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
