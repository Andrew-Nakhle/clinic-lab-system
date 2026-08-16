<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id'=>['required','exists:patient_profiles,id'],
            'doctor_id'=>['required','exists:doctor_profiles,id'],
            'appointment_id'=>['required','exists:appointments,id'],
            'report'=>['required','string'],
            'medical_notes'=>['nullable','string'],

            'prescription' => ['nullable', 'array'],
                'prescription.*.medicine_name' => ['required', 'string'],
            'prescription.*.instructions' => ['required', 'string'],
            'report_images' => ['nullable', 'array'],
            'report_images.*' => ['image', 'mimes:jpg,jpeg,png'],
        ];
    }
}
