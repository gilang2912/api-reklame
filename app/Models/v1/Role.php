<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['role_name'];

    public $timestamps = false;
}
