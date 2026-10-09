<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class DoctorResource extends JsonResource
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
            'title' => $this->title,
            'avatar_url' => $this->avatar_url,
            'specialty' => $this->whenLoaded('specialty', fn () => $this->specialty === null ? null : [
                'id' => $this->specialty->id,
                'name' => $this->specialty->name,
                'slug' => $this->specialty->slug,
            ]),
            // Independent doctors run their own practice, which is the facility
            // the referral is attributed to.
            'practice' => $this->whenLoaded('hospital', fn () => $this->hospital === null ? null : [
                'id' => $this->hospital->id,
                'name' => $this->hospital->name,
            ]),
        ];
    }
}
