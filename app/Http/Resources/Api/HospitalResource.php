<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalResource extends JsonResource
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
            'name' => $this->name,
            'short_name' => $this->short_name,
            'code' => $this->code,
            'location' => $this->location,
            'phone' => $this->phone,
            'email' => $this->email,
            'level' => $this->level,
            'is_active' => $this->is_active,
        ];
    }
}
