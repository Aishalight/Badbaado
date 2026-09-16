<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'title' => $this->title,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'hospital_id' => $this->hospital_id,
            'role' => $this->whenLoaded('role', fn () => [
                'slug' => $this->role->slug,
                'name' => $this->role->name,
            ]),
            'hospital' => new HospitalResource($this->whenLoaded('hospital')),
        ];
    }
}
