<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterSecretaryRequest extends FormRequest
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
            'email' => ['required', 'string', 'email','unique:users,email'],
            'password' => ['required', 'string','confirmed'],
            'gender'=>['required','string','in:male,female,other'],
            'section_id' => ['required','exists:sections,id' ],
            'birth_date'=>['required','date'],
        ];
    }
}
