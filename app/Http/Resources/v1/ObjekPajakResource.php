<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class ObjekPajakResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'npwpd' => $this->npwpd,
            'kd_op' => $this->kd_objek_pajak,
            'nm_wp' => $this->nama_wp,
            'objek_pajak' => $this->objek_pajak,
            'lokasi_objek' => $this->lokasi_objek,
            'jns_reklame' => $this->jns_reklame,
            'kecamatan' => $this->kecamatan,
            'panjang' => $this->panjang,
            'lebar' => $this->lebar,
            'tinggi' => $this->tinggi,
            'keterangan' => $this->keterangan,
            'coordinate' => [
                'long' => $this->geolocation->longitude,
                'lat' => $this->geolocation->latitude,
            ],
            'images' => $this->objekimage
        ];
    }
}
