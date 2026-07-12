<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'blood_group'=>$this->blood_group,
            'weight'=>$this->weight,
            'tall'=>$this->tall,
            'section_id'=>$this->section_id,
            'id_card'=>$this->id_card,
            'profile_image_url' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
            'user' => $this->whenLoaded('user'),
        ];
    }
}
