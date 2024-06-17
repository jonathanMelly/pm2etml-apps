<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupName extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'year'];

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function periods(): HasManyThrough
    {
        return $this->hasManyThrough(AcademicPeriod::class, Group::class);
    }

    public static function guessGroupNameYear(string $groupName): int
    {
        if (str_contains($groupName, 'msig')) {
            return 1;
        }
        $numbers = preg_replace('/[^0-9]/', '', $groupName);
        if ($numbers == '') {
            $numbers = 1;
        }

        return $numbers;
    }
}
