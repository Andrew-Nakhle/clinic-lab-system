<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class LabRequestResource extends JsonResource
{public function toArray($request)
{
    // تحميل العلاقات بوضوح
    $doctorUser = $this->whenLoaded('doctor') ? $this->doctor->user : null;
    $patientUser = $this->whenLoaded('patient') ? $this->patient->user : null;

    return [
        'request_id'   => $this->id,
        'status'       => $this->status,
        'doctor_notes' => $this->doctor_notes,

        // استخدام البيانات التي استخرجناها للتو
        'doctor_name'  => $doctorUser ? ($doctorUser->first_name . ' ' . $doctorUser->last_name) : 'غير معروف',
        'patient_name' => $patientUser ? ($patientUser->first_name . ' ' . $patientUser->last_name) : 'غير معروف',

        'tests'        => $this->tests->map(function ($test) {
            return [
                'name'   => $test->display_name,
                'normal_range' => $test->normal_range,
                'result' => $test->pivot->result_value ?? null,
            ];
        }),
    ];
}
}

