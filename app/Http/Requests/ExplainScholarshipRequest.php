<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExplainScholarshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Only an id is accepted: the server loads the scholarship itself. */
    public function rules(): array
    {
        return [
            'scholarship_id' => ['required', 'integer', 'exists:scholarships,id'],
        ];
    }
}
