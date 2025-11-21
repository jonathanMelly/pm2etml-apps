<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemarkTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'text',
        'user_id',
        'criterion_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }
}
