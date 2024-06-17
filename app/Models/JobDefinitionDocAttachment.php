<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parental\HasParent;

class JobDefinitionDocAttachment extends Attachment
{
    use HasParent;

    //fixed attachable...using STI
    public function jobDefinition(): BelongsTo
    {
        return $this->belongsTo(JobDefinition::class, 'attachable_id');
    }
}
