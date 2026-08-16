<?php

namespace App\Http\Requests\Patient;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientProfileRequest extends FormRequest
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
            // users
            'first_name' => ['sometimes', 'string'],
            'last_name' => ['sometimes', 'string'],
            'phone'=>['sometimes','string','regex:/^([0-9\s\-\+\(\)]*)$/','unique:users,phone,' . auth()->id()],
            'gender'=>['sometimes','string','in:male,female,other'],
            'birth_date' => ['sometimes', 'date'],

            'profile_image' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png'
            ],

            // patient_profiles
            'tall' => ['sometimes', 'integer'],
            'weight' => ['sometimes', 'integer'],
            'blood_group' => ['sometimes', 'string','in:A+,A-,B+,B-,AB+,AB-,O+,O-'],

            'id_card' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png',

            ],
        ];
    }
}
