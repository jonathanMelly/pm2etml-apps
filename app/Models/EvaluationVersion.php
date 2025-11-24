<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'version_number',
        'evaluator_type',
        'created_by_user_id',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getGeneralRemarkAttribute()
    {
        return $this->comments()->latest()->first();
    }

    public function appreciations()
    {
        return $this->hasMany(AppreciationVersion::class, 'version_id');
    }

    public function getVersionNameAttribute()
    {
        return $this->evaluator_type === 'teacher' 
            ? 'Formative ' . $this->version_number 
            : 'Self eval ' . $this->version_number;
    }
}
