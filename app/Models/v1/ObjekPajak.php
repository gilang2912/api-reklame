<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

class ObjekPajak extends Model
{
    protected $table = 'objek_pajak';

    protected $fillable = [
        'npwpd',
        'kd_objek_pajak',
        'nama_wp',
        'objek_pajak',
        'lokasi_objek',
        'jns_reklame',
        'kecamatan',
        'panjang',
        'lebar',
        'tinggi'
    ];

    public function geolocation()
    {
        return $this->hasOne(Geolocation::class, 'kd_objek_pajak', 'kd_objek_pajak');
    }

    public function objekImage()
    {
        return $this->hasMany(ObjekImage::class, 'kd_objek_pajak', 'kd_objek_pajak');
    }
}
