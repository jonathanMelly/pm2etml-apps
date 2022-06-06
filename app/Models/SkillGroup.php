<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\SkillGroup
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Skill[] $skills
 * @property-read int|null $skills_count
 * @method static Builder|SkillGroup newModelQuery()
 * @method static Builder|SkillGroup newQuery()
 * @method static \Illuminate\Database\Query\Builder|SkillGroup onlyTrashed()
 * @method static Builder|SkillGroup query()
 * @method static Builder|SkillGroup whereCreatedAt($value)
 * @method static Builder|SkillGroup whereDeletedAt($value)
 * @method static Builder|SkillGroup whereId($value)
 * @method static Builder|SkillGroup whereName($value)
 * @method static Builder|SkillGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|SkillGroup withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SkillGroup withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class SkillGroup extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['name'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('orderByName', function (Builder $builder) {
            $builder->orderBy('name', 'asc');
        });
    }

    public function skills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Skill::class);
    }
}
