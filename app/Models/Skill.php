<?php

namespace App\Models;

use App\Exceptions\BadFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kirschbaum\PowerJoins\PowerJoins;

class Skill extends Model
{
    use PowerJoins, SoftDeletes;

    public const SEPARATOR = ':';

    protected $fillable = ['name', 'skill_group_id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('orderByName', function (Builder $builder) {
            $builder
                ->orderByPowerJoins('skillGroup.name')
                ->orderBy('name', 'asc');
        });
    }

    public function skillGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SkillGroup::class);
    }

    public function jobDefinitions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(JobDefinition::class);
    }

    public static function firstOrCreateFromString(string $nameAndGroup,
        string $separator = self::SEPARATOR): Model|Skill
    {
        if (! preg_match('/.+:.+/', $nameAndGroup)) {
            throw new BadFormatException(__('Wrong skill format, expected ...:...'));
        }
        $parts = explode($separator, $nameAndGroup);

        return self::firstOrCreateWithGroup($parts[0], $parts[1], $separator);

    }

    public static function firstOrCreateWithGroup(string $skillGroup,
        string $skillName,
        string $separator = self::SEPARATOR): Model|Skill
    {
        $skillGroup = SkillGroup::firstOrCreate(['name' => trim($skillGroup)]);

        return Skill::withoutGlobalScope('orderByName')
            ->firstOrCreate([
                'name' => trim($skillName),
                'skill_group_id' => $skillGroup->id,
            ]);
    }

    public function getFullName(): string
    {
        return $this->skillGroup->name.self::SEPARATOR.' '.$this->name;
    }
}
