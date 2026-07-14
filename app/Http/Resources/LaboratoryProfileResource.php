<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LaboratoryProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'license_number' => $this->license_number,
            'image'          => $this->image ? asset('storage/' . $this->image) : null,

            // بيانات المستخدم المرتبط (عبر علاقة user)
            'user' => [
                'full_name' => $this->user ? ($this->user->first_name . ' ' . $this->user->last_name) : null,
                'email'     => $this->user->email ?? null,
                'phone'     => $this->user->phone ?? null,
                'status'     => $this->user->status ?? null,
            ],

            // بيانات القسم المرتبط (عبر علاقة section)
            'section' => $this->section ? $this->section->name : 'غير محدد',
        ];
    }
}
