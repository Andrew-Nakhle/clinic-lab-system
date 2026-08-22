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
            'consultation_fee'=>$this->consultation_fee,
//            'profile_image_url' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
            'certifications' => $this->whenLoaded('certifications', function () {
                return $this->certifications->map(function ($certification) {
                    return [
                        'id' => $certification->id,
                        'certification_url' => url(
                            'storage/' . $certification->certification
                        ),
                    ];
                });
            }),

            'service_areas' => $this->whenLoaded('serviceAreas', function () {
                return $this->serviceAreas->map(function ($serviceArea) {
                    return [
                        'id' => $serviceArea->area->id,
                        'name' => $serviceArea->area->name,
                    ];
                });
            }),
            'section' => $this->whenLoaded('section', function () {
                return [
                    'id' => $this->section->id,
                    'name' => $this->section->name,
                ];
            }),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'full_name' => $this->user->first_name . ' ' . $this->user->last_name,
                    'phone' => $this->user->phone,
                    'email' => $this->user->email,
                    'gender' => $this->user->gender,
                    'birth_date' => $this->user->birth_date,

                    'profile_image_url' => $this->user->profile_image
                        ? url('storage/' . $this->user->profile_image)
                        : null,
                ];
            }),
            'schedule' => $this->whenLoaded('schedules'),

        ];
    }
}
