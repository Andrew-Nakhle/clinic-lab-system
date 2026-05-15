<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterPatientRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255','min:2'],
            'last_name' => ['required', 'string', 'max:255','min:2'],
            'phone'=>['required','string','regex:/^([0-9\s\-\+\(\)]*)$/','unique:users,phone'],
            'password' => ['required', 'string','confirmed'],
            'gender'=>['required','string','in:male,female,other'],
            'profile_image'=>['image','nullable'],
            'id_card'=>['required','image'],
            'blood_group'=>['required','string'],
            'tall'=>['required','string'],
            'weight'=>['required','string'],
            'birth_date'=>['required','date'],



        ];
    }
}
