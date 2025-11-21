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
        'created_by_user_id',
        'general_remark_id',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function generalRemark()
    {
        return $this->belongsTo(Remark::class, 'general_remark_id');
    }

    public function appreciations()
    {
        return $this->hasMany(AppreciationVersion::class, 'version_id');
    }
}
