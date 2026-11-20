<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplySuggestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Only positions are accepted. The text itself never comes from the browser. */
    public function rules(): array
    {
        return [
            'dismiss' => ['sometimes', 'boolean'],
            'languages' => ['sometimes', 'array'],
            'languages.*' => ['integer', 'min:0'],
            'experiences' => ['sometimes', 'array'],
            'experiences.*' => ['integer', 'min:0'],
        ];
    }
}
