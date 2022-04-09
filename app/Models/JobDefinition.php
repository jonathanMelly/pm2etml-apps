<?php

namespace App\Models;

use App\Enums\JobPriority;
use App\Enums\RequiredTimeUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JetBrains\PhpStorm\Pure;

/**
 * App\Models\JobDefinition
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $description
 * @property int $required_xp_years
 * @property JobPriority $priority
 * @property int $max_workers
 * @property string|null $published_date
 * @property int $allocated_time
 * @property RequiredTimeUnit $allocated_time_unit
 * @property string $image
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Contract[] $contracts
 * @property-read int|null $contracts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $providers
 * @property-read int|null $providers_count
 * @method static \Database\Factories\JobDefinitionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition newQuery()
 * @method static \Illuminate\Database\Query\Builder|JobDefinition onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereAllocatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereAllocatedTimeUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereMaxWorkers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition wherePublishedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereRequiredXpYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobDefinition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|JobDefinition withTrashed()
 * @method static \Illuminate\Database\Query\Builder|JobDefinition withoutTrashed()
 * @mixin \Eloquent
 */
class JobDefinition extends Model
{
    use HasFactory,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'required_xp_years',
        'priority',
        'status',
        'published_date',
        'image',
        'required_time',
        'required_time_unit'
    ];

    protected $casts = [
        'priority'=> JobPriority::class,
        'allocated_time_unit'=>RequiredTimeUnit::class
    ];

    public static function published(): JobDefinition|\Illuminate\Database\Eloquent\Builder
    {
        return JobDefinition::where('published_date','<=',now());
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)/*->as("providers")*/->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function getAllocatedTime(RequiredTimeUnit $targetUnit=RequiredTimeUnit::HOUR) : float
    {
        return round(RequiredTimeUnit::Convert($this->allocated_time,$this->allocated_time_unit,$targetUnit),0);
    }

}
