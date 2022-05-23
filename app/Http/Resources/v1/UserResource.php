<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'nama' => $this->nama,
            'username' => $this->username,
            'nip' => $this->nip,
            'last_login' => $this->last_login,
            'created_at' => ($this->created_at)->format('d-m-Y'),
            'updated_at' => ($this->updated_at)->format('d-m-Y')
        ];
    }
}
