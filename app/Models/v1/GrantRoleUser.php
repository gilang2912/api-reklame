<?php

namespace App\Models\v1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GrantRoleUser extends Model
{
    protected $table = 'grant_role_users';

    protected $fillable = ['user_id', 'role_id'];

    public function users()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'role_id', 'role_id');
    }
}
