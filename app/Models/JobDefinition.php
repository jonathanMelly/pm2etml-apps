<?php

namespace App\Models;

use App\Enums\CustomPivotTableNames;
use App\Enums\JobPriority;
use App\Enums\RequiredTimeUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

/**
 * App\Models\JobDefinition
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $description
 * @property int $required_xp_years
 * @property JobPriority $priority
 * @property int $max_workers
 * @property \Illuminate\Support\Carbon|null $published_date
 * @property int $allocated_time
 * @property RequiredTimeUnit $allocated_time_unit
 * @property int $one_shot
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\JobDefinitionDocAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Contract[] $contracts
 * @property-read int|null $contracts_count
 * @property-read \App\Models\JobDefinitionMainImageAttachment|null $image
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $providers
 * @property-read int|null $providers_count
 * @method static Builder|JobDefinition available()
 * @method static \Database\Factories\JobDefinitionFactory factory(...$parameters)
 * @method static Builder|JobDefinition newModelQuery()
 * @method static Builder|JobDefinition newQuery()
 * @method static \Illuminate\Database\Query\Builder|JobDefinition onlyTrashed()
 * @method static Builder|JobDefinition published()
 * @method static Builder|JobDefinition query()
 * @method static Builder|JobDefinition whereAllocatedTime($value)
 * @method static Builder|JobDefinition whereAllocatedTimeUnit($value)
 * @method static Builder|JobDefinition whereCreatedAt($value)
 * @method static Builder|JobDefinition whereDeletedAt($value)
 * @method static Builder|JobDefinition whereDescription($value)
 * @method static Builder|JobDefinition whereId($value)
 * @method static Builder|JobDefinition whereMaxWorkers($value)
 * @method static Builder|JobDefinition whereOneShot($value)
 * @method static Builder|JobDefinition wherePriority($value)
 * @method static Builder|JobDefinition wherePublishedDate($value)
 * @method static Builder|JobDefinition whereRequiredXpYears($value)
 * @method static Builder|JobDefinition whereTitle($value)
 * @method static Builder|JobDefinition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|JobDefinition withTrashed()
 * @method static \Illuminate\Database\Query\Builder|JobDefinition withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class JobDefinition extends Model
{
    use HasFactory,SoftDeletes;

    public const MIN_PERIODS=30;
    public const MAX_PERIODS=150;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'required_xp_years',
        'priority',
        'status',
        'published_date',
        'image_attachment_id',
        'allocated_time',
        'one_shot'
    ];

    protected $casts = [
        'priority'=> JobPriority::class,
        'allocated_time_unit'=>RequiredTimeUnit::class,
        'published_date'=> 'datetime'
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published_date','<=',now());
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('one_shot','=',false)
            ->orWhereDoesntHave('contracts');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
        //->with('workers.group.groupName',fn($q)=>$q->orderBy('name'))
        //->with('workers.user',fn($q)=>$q->orderBy('lastname')->orderBy('firstname'));
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, CustomPivotTableNames::USER_JOB_DEFINITION->value)
            /*->as("providers")*/
            ->withTimestamps();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(JobDefinitionDocAttachment::class,'attachable');
    }

    public function image():MorphOne
    {
        return $this->morphOne(related:JobDefinitionMainImageAttachment::class,
            name: 'attachable');
    }

    #[Pure] public function getAllocatedTime(RequiredTimeUnit $targetUnit = RequiredTimeUnit::HOUR): float
    {
        if($this->allocated_time===null) {
            return 0;
        }
        return round(RequiredTimeUnit::Convert($this->allocated_time, $this->allocated_time_unit, $targetUnit), 0);
    }

    #[Pure] public function getAllocationDetails(): string
    {
        return $this->getAllocatedTime() . 'h / '
            . $this->getAllocatedTime(RequiredTimeUnit::PERIOD) . 'p';
    }

    public function isPublished():bool
    {
        return $this->published_date!==null && $this->published_date<=today();
    }

}
