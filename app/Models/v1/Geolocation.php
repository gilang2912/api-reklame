<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class Geolocation extends Model
{
    protected $fillable = [
        'kd_objek_pajak', 'longitude', 'latitude'
    ];

    public function objekPajak()
    {
        return $this->belongsTo(ObjekPajak::class, 'kd_objek_pajak', 'kd_objek_pajak');
    }
}
