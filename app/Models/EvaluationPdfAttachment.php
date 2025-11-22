<?php

namespace App\Models;

use App\Traits\EncryptedAttachment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parental\HasParent;

class EvaluationPdfAttachment extends Attachment
{
    use HasParent, EncryptedAttachment;

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'attachable_id');
    }
}
