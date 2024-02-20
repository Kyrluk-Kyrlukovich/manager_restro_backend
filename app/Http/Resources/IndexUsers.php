<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexUsers extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            "name" => $this->first_name.' '.$this->last_name.($this->patronomic ? ' '.$this->patronomic : ''),
            "phone" => $this->phone,
            "role" => $this->roles,
            "role_name" => $this->role()->first()->role ,
            "email" => $this->email,
            "salary" => $this->salary,
            "avatar" => $this->avatar,
            "status" => $this->status,
        ];
    }
}
