<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions'; 

    protected $fillable = ['name'];

    // Définir la relation avec `RoleHasPermission`
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }
}
