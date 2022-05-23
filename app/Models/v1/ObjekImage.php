<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class ObjekImage extends Model
{
    protected $table = 'objek_images';

    protected $fillable = [
        'kd_objek_pajak', 'path_name'
    ];

    public function objekPajak()
    {
        return $this->belongsTo(objekPajak::class, 'kd_objek_pajak', 'kd_objek_pajak');
    }
}
