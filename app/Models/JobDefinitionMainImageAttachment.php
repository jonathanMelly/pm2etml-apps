<?php

namespace App\Models;

use App\Constants\MorphTargets;
use App\Exceptions\DataIntegrityException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Parental\HasParent;

class JobDefinitionMainImageAttachment extends Attachment
{
    use HasParent, HasFactory;

    /**
     * @throws DataIntegrityException
     */
    public function attachJobDefinition(JobDefinition $jobDefinition, bool $update=true):JobDefinitionMainImageAttachment
    {
        //Only 1 image per jobdef
        if(JobDefinitionMainImageAttachment::query()
            ->where('attachable_id','=',$jobDefinition->id)->exists())
        {
            throw new DataIntegrityException('Job with id '.$jobDefinition->id.' already has an attached image. Please delete current image first and retry');
        }
        return parent::attachJobDefinition($jobDefinition,$update);
    }
}
