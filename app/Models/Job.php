<?php

namespace App\Models;

use App\Enums\JobPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Job
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
 * @property string $image
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Attachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Contract[] $contracts
 * @property-read int|null $contracts_count
 * @method static \Database\Factories\JobFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job newQuery()
 * @method static \Illuminate\Database\Query\Builder|Job onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereMaxWorkers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job wherePublishedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereRequiredXpYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Job withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Job withoutTrashed()
 * @mixin \Eloquent
 */
class Job extends Model
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
        'image'
    ];

    protected $casts = [
        'priority'=> JobPriority::class
    ];

    public static function publishedJobs(): Job|\Illuminate\Database\Eloquent\Builder
    {
        return Job::where('published_date','<=',now());
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class,"clients")->as("clients")->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

}
