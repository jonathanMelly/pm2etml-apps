<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppreciationVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'version_id',
        'criterion_id',
        'value',
        'is_ignored',
    ];

    public function version()
    {
        return $this->belongsTo(EvaluationVersion::class, 'version_id');
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class, 'criterion_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getRemarkAttribute()
    {
        return $this->comments()->latest()->first();
    }
}
