<?php

namespace App\Http\Requests\Auth;

use App\Enums\Doctor\DayOfWeek;
use App\Enums\Schedule\ScheduleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterDoctorRequest extends FormRequest
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
            'schedules' => ['required', 'array', 'min:1'],


            'schedules.*.day_of_week' => [
                'required',
                Rule::enum(DayOfWeek::class),
            ],

            'schedules.*.start_time' => [
                'required',
                'date_format:H:i',
            ],

            'schedules.*.end_time' => [
                'required',
                'date_format:H:i',
                'after:schedules.*.start_time'
            ],
            'schedules.*.schedule_type'=>[
                'required',
                Rule::enum(ScheduleType::class)
            ],

            'service_areas'=>['nullable', 'array'],
            'service_areas.*'=>['exists:areas,id'],
                'first_name' => ['required', 'string', 'max:255','min:2'],
                'last_name' => ['required', 'string', 'max:255','min:2'],
                'phone'=>['required','string','regex:/^([0-9\s\-\+\(\)]*)$/','unique:users,phone'],
                'email' => ['required', 'string', 'email','unique:users,email'],
                'password' => ['required', 'string','confirmed'],
                'gender'=>['required','string','in:male,female,other'],
                'profile_image'=>['required','image'],
                'birth_date'=>['required','date'],
                'section_id'=>['required','exists:sections,id','integer'],
                'certification'=>['required','image'],
                'experience_years'=>['required','integer'],

        ];
    }
}
