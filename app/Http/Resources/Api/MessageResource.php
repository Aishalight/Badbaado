<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'referral_id' => $this->referral_id,
            'body' => $this->body,
            'read' => $this->read_at !== null,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'sender' => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
