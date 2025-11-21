<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'author_user_id',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
