<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'kd_op' => $this->kd_objek_pajak,
            'no_reg' => rtrim($this->no_registrasi),
            'nm_wp' => $this->nm_wp,
            'objek_pajak' => $this->objek_pajak,
            'lokasi_reklame' => $this->alamat,
            'jns_reklame' => $this->jns_reklame,
            'kecamatan' => $this->kecamatan,
            'panjang' => (int) $this->panjang,
            'lebar' => (int) $this->lebar,
            'tinggi' => (int) $this->tinggi,
        ];
    }
}
