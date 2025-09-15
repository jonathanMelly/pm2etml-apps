<?php

namespace App\Models;

use App\Constants\AttachmentTypes;
use App\Constants\FileFormat;
use App\Constants\MorphTargets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Parental\HasChildren;

class Attachment extends Model
{
    use HasChildren, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'storage_path',
        'size',
        'type', //for STI
        'attachable_type',
        'attachable_id',
    ];

    protected $childTypes = [
        AttachmentTypes::STI_JOB_DEFINITION_MAIN_IMAGE_ATTACHMENT => JobDefinitionMainImageAttachment::class,
        AttachmentTypes::STI_JOB_DEFINITION_ATTACHMENT => JobDefinitionDocAttachment::class,
        AttachmentTypes::STI_CONTRACT_EVALUATION_ATTACHMENT => ContractEvaluationAttachment::class,
    ];

    public static function boot()
    {
        parent::boot();
        static::deleted(function (Attachment $attachment) {
            if ($attachment->isForceDeleting()) {
                info('Permanently deleting '.$attachment->storage_path);
                uploadDisk()->delete($attachment->storage_path);
            } else {
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

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if this attachment should be encrypted based on traits used
     */
    public function shouldBeEncrypted(): bool
    {
        return in_array(\App\Traits\EncryptedAttachment::class, class_uses_recursive($this));
    }

    /**
     * Get file content (encrypted or regular based on child class traits)
     */
    public function getFileContent(): string
    {
        if ($this->shouldBeEncrypted()) {
            return $this->getDecrypted();
        }
        
        return uploadDisk()->get($this->storage_path);
    }

    public function attachJobDefinition(JobDefinition $jobDefinition, bool $update = true): Attachment
    {
        if (! uploadDisk()->exists($this->storage_path)) {
            app('log')->error('Missing attachment related file',
                ['file' => $this->storage_path,
                    'full path' => uploadDisk()->path($this->storage_path)]);
            abort(500, 'Missing attachment related file');
        }

        //Move from pending to final
        $newPath = dirname($this->storage_path, 2).DIRECTORY_SEPARATOR.basename($this->storage_path);
        if (uploadDisk()->move($this->storage_path, $newPath)) {
            $this->storage_path = $newPath;
            $this->attachable_type = MorphTargets::MORPH2_JOB_DEFINITION;
            $this->attachable_id = $jobDefinition->id;

            if ($update) {
                $this->update();
            }

            return $this;
        } else {
            app('log')->error('Cannot move attachment from pending to attached folder...',
                ['old path' => $this->storage_path, 'new path' => $newPath]);
            abort(500, 'Cannot move attachment from pending to attached folder...');
        }

    }
}
