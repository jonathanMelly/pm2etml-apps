<?php

namespace App\Models;

use App\Traits\EncryptedAttachment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parental\HasParent;

class ContractEvaluationAttachment extends Attachment
{
    use HasParent, EncryptedAttachment;

    public function workerContract(): BelongsTo
    {
        return $this->belongsTo(WorkerContract::class, 'attachable_id');
    }
}
