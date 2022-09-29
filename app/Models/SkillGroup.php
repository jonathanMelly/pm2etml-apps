<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
