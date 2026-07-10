<?php

namespace App\Http\Resources\Appointment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        {
            return [
                'id' => $this->id,

                'patient_name' => $this->patient->user->first_name . ' ' . $this->patient->user->last_name,

                'patient_phone' => $this->patient->user->phone,

                'start_at' => $this->start_at,

                'end_at' => $this->end_at,

                'status' => $this->status,

                'price' => $this->price,

                'made_by' => $this->made_by,
            ];
        }
    }
}
