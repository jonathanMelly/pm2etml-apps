<?php

namespace App\Models;

use App\Constants\AttachmentTypes;
use App\Constants\FileFormat;
use App\Constants\MorphTargets;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use http\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Facades\Storage;
use Parental\HasChildren;

/**
 * App\Models\Attachment
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
 * @property-read Model|\Eloquent $attachable
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment newQuery()
 * @method static \Illuminate\Database\Query\Builder|Attachment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereAttachableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereAttachableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereStoragePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Attachment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Attachment withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Attachment extends Model
{
    use SoftDeletes, HasChildren;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'storage_path',
        'size',
        'type',//for STI
        'attachable_type',
        'attachable_id'
    ];

    protected $childTypes = [
        AttachmentTypes::STI_JOB_DEFINITION_MAIN_IMAGE_ATTACHMENT => JobDefinitionMainImageAttachment::class,
        AttachmentTypes::STI_JOB_DEFINITION_ATTACHMENT => JobDefinitionDocAttachment::class,
    ];

    public static function boot()
    {
        parent::boot();
        static::deleted(function(Attachment $attachment)
        {
            if($attachment->isForceDeleting())
            {
                info('Permanently deleting '.$attachment->storage_path);
                uploadDisk()->delete($attachment->storage_path);
            }
            else
            {
                //Move to deleted subfolder
                $newPath = dirname($attachment->storage_path).
                    DIRECTORY_SEPARATOR.FileFormat::ATTACHMENT_DELETED_SUBFOLDER.
                    DIRECTORY_SEPARATOR.basename($attachment->storage_path);
                uploadDisk()
                    ->move(
                        $attachment->storage_path,
                        $newPath);

                $attachment->update(['storage_path' => $newPath]);
            }

        });
    }

    public function attachable() : MorphTo
    {
        return $this->morphTo();
    }

    public function attachJobDefinition(JobDefinition $jobDefinition, bool $update=true): Attachment
    {
        if(!uploadDisk()->exists($this->storage_path))
        {
            app('log')->error('Missing attachment related file',
                ['file'=>$this->storage_path,
                    'full path'=>uploadDisk()->path($this->storage_path)]);
            abort(500,'Missing attachment related file');
        }

        //Move from pending to final
        $newPath = dirname($this->storage_path,2).DIRECTORY_SEPARATOR.basename($this->storage_path);
        if(uploadDisk()->move($this->storage_path,$newPath))
        {
            $this->storage_path = $newPath;
            $this->attachable_type=MorphTargets::MORPH2_JOB_DEFINITION;
            $this->attachable_id=$jobDefinition->id;

            if($update)
            {
                $this->update();
            }

            return $this;
        }
        else
        {
            app('log')->error('Cannot move attachment from pending to attached folder...',
                ['old path'=>$this->storage_path,'new path'=>$newPath]);
            abort(500,'Cannot move attachment from pending to attached folder...');
        }

    }

}
