<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralResource extends JsonResource
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
            'referral_number' => $this->referral_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'urgency' => $this->urgency?->value,
            'urgency_label' => $this->urgency?->label(),
            'is_emergency' => $this->is_emergency,
            'department' => $this->department,
            'referral_reason' => $this->referral_reason,
            'symptoms' => $this->symptoms,
            'vitals' => $this->vitals,
            'consciousness' => $this->consciousness,
            'trauma_indicator' => $this->trauma_indicator,
            'existing_conditions' => $this->existing_conditions,
            'current_interventions' => $this->current_interventions,
            'notes' => $this->notes,
            'ai_suggestion' => $this->ai_suggestion,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'referring_hospital' => new HospitalResource($this->whenLoaded('referringHospital')),
            'receiving_hospital' => new HospitalResource($this->whenLoaded('receivingHospital')),
            'referring_user' => new UserResource($this->whenLoaded('referringUser')),
            'coordinator' => new UserResource($this->whenLoaded('coordinator')),
            'patient' => $this->whenLoaded('patient'),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'mime' => $attachment->mime,
                'size' => $attachment->size,
                'created_at' => $attachment->created_at,
            ])),
        ];
    }
}
