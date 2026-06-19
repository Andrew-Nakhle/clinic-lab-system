<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        function rules(): array
        {
            return [
                'first_name' => ['sometimes', 'string', 'min:2', 'max:255'],
                'last_name' => ['sometimes', 'string', 'min:2', 'max:255'],

                'phone' => ['sometimes', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'unique:users,phone,' . auth()->id()],//هي يعني ماعدا المستخدم الحالي
                'gender' => ['sometimes','in:male,female,other'],
                'birth_date' => ['sometimes', 'date'],
                'profile_image' => ['sometimes', 'image'],
                'section_id' => ['sometimes', 'integer', 'exists:sections,id'],
                'certification' => ['sometimes', 'image'],
                'experience_years' => ['sometimes', 'integer'],
                'current_password' => ['required_with:password', 'current_password'],
                'password' => ['sometimes', 'confirmed', 'min:8'],
                'bio' => ['sometimes', 'text'],
            ];
        }
    }
}

