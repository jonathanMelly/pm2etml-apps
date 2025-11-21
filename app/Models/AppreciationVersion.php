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
        'remark_id',
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

    public function remark()
    {
        return $this->belongsTo(Remark::class, 'remark_id');
    }
}
