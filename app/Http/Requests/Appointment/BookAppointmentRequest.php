<?php

namespace App\Http\Requests\Appointment;

use App\Enums\Appointment\AppointmentType;
use App\Enums\Payment\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookAppointmentRequest extends FormRequest
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
            'doctor_id' => ['required', 'integer', 'exists:doctor_profiles,id'],

            'start_at' => ['required', 'date', 'after:now'],

            'appointment_type' => ['required', Rule::enum(AppointmentType::class),],

            'payment_method' => ['required', Rule::enum(PaymentMethod::class),],

        ];
    }
}
