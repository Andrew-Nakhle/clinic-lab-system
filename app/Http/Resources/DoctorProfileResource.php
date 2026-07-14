<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'full_name' => $this->user ? ($this->user->first_name . ' ' . $this->user->last_name) : 'غير معروف',
            'profile'   => [
                'specialization'   => $this->specialization,
                'experience_years' => $this->experience_years,
                'bio'              => $this->bio,
                'section_id'       => $this->section_id,
                'profile_image'    => $this->formatUrl($this->profile_image),
                'certification'    => $this->formatUrl($this->certification),
            ],
        ];
    }

    private function formatUrl(?string $path): ?string
    {
        if (!$path || str_contains($path, 'tmp')) return null;
        return url('storage/' . ltrim($path, '/'));
    }
}
