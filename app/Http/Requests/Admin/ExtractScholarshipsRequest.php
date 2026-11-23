<?php

namespace App\Http\Requests\Admin;

use App\Services\ScholarshipExtractionService;
use Illuminate\Foundation\Http\FormRequest;

class ExtractScholarshipsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:30', 'max:'.ScholarshipExtractionService::SOURCE_MAX_CHARS],
        ];
    }
}
