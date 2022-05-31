<?php

namespace App\Models;

use App\Constants\MorphTargets;
use App\Exceptions\DataIntegrityException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Parental\HasParent;

/**
 * App\Models\JobDefinitionMainImageAttachment
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $storage_path
 * @property int $size
 * @property string|null $attachable_type
 * @property int|null $attachable_id
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $attachable
 * @method static \Database\Factories\JobDefinitionMainImageAttachmentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment newQuery()
 * @method static \Illuminate\Database\Query\Builder|JobDefinitionMainImageAttachment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereAttachableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereAttachableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereStoragePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionMainImageAttachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|JobDefinitionMainImageAttachment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|JobDefinitionMainImageAttachment withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
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
