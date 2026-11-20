<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\UserStudyPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'campus' => ['nullable', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'age' => ['nullable', 'integer', 'min:15', 'max:60'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'has_financial_need' => ['nullable', 'boolean'],
            'study_field_category' => ['nullable', 'string', Rule::in(UserStudyPlan::FIELDS)],
            'preferred_country' => ['nullable', 'string', Rule::in(UserStudyPlan::LOCATIONS)],
        ];
    }
}
