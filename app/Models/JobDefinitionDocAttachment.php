<?php

namespace App\Models;

use App\Constants\MorphTargets;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parental\HasParent;

/**
 * App\Models\JobDefinitionAttachment
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
 * @property-read \App\Models\JobDefinition|null $jobDefinition
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment newQuery()
 * @method static \Illuminate\Database\Query\Builder|JobDefinitionDocAttachment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereAttachableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereAttachableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereStoragePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinitionDocAttachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|JobDefinitionDocAttachment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|JobDefinitionDocAttachment withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class JobDefinitionDocAttachment extends Attachment
{
    use HasParent;

    //fixed attachable...using STI
    public function jobDefinition(): BelongsTo
    {
        return $this->belongsTo(JobDefinition::class,'attachable_id');
    }
}
