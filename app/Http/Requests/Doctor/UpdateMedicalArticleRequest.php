<?php

namespace App\Http\Requests\Doctor;

use App\Enums\Article\MedicalArticleCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateMedicalArticleRequest extends FormRequest
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
            'title' => ['sometimes', 'string'],
            'content' => ['sometimes', 'string'],

                'category' => [
                'sometimes',
                new Enum(MedicalArticleCategory::class),
            ],

            'image' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png',
            ],
        ];
    }
}
