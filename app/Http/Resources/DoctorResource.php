<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'experience_years' => $this->experience_years,
            'bio' => $this->bio,
            'qualification' => $this->qualification,
            'specialization' => $this->specialization,
            'profile_image_url' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
            'certification_url' => $this->certification ? asset('storage/' . $this->certification) : null,
            'user' => $this->whenLoaded('user'),
        ];
    }
}
