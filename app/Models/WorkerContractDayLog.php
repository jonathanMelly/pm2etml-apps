<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerContractDayLog extends Model
{
    protected $fillable = [
        'worker_contract_id',
        'date',
        'periods_count',
        'precision_minutes',
        'periods',
        'student_notes',
        'appreciation',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'periods' => 'array',
        ];
    }

    public function workerContract(): BelongsTo
    {
        return $this->belongsTo(WorkerContract::class);
    }
}

