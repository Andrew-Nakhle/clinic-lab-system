<?php

namespace App\Http\Resources\Auth;

use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'full_name'  => $this->first_name . ' ' . $this->last_name,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'gender'     => $this->gender,
            'birth_date' => $this->birth_date,
            'role'       => $this->getRoleNames()->first(),
            'profile'    => $this->getProfileData(),
        ];
    }

    private function getProfileData(): ?array
    {
        // دالة مساعدة لمعالجة المسارات (تحويل المسار إلى رابط كامل)
        $getImageUrl = fn($path) => $path ? url('storage/' . ltrim($path, '/')) : null;

        // 1. بروفايل الطبيب
        if ($this->hasRole('doctor') && $this->doctor) {
            return [
                'type'             => 'doctor',
                'profile_image'    => $getImageUrl($this->doctor->profile_image),
                'specialization'   => $this->doctor->specialization,
                'experience_years' => $this->doctor->experience_years,
                'certification'    => $getImageUrl($this->doctor->certification),
                'bio'              => $this->doctor->bio,
                'section_id'       => $this->doctor->section_id,
            ];
        }

        // 2. بروفايل المريض
        if ($this->hasRole('patient') && $this->patient) {
            return [
                'type'                       => 'patient',
                'tall'                       => $this->patient->tall,
                'weight'                     => $this->patient->weight,
                'blood_group'                => $this->patient->blood_group,
                'profile_image'              => $getImageUrl($this->patient->profile_image),
                'id_card'                    => $getImageUrl($this->patient->id_card),
                'medical_record_access_code' => $this->patient->medical_record_access_code,
            ];
        }

        // 3. بروفايل السكرتير
        if ($this->hasRole('secretary') && $this->secretary) {
            return [
                'type'  => 'secretary',
                'image' => $getImageUrl($this->secretary->image),
            ];
        }

        return null;
    }
}
