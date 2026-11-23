<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

/**
 * Validation + list normalisation shared by the admin form and the AI import
 * review, so reviewed AI output is held to exactly the same rules as hand entry.
 */
class ScholarshipRules
{
    public const LIST_FIELDS = ['majors', 'allowed_nationalities', 'required_documents'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(mixed $year, ?int $ignoreId = null): array
    {
        $uniqueName = Rule::unique('scholarships', 'name')
            ->where(fn (Builder $query) => $query->where('year', $year));

        if ($ignoreId !== null) {
            $uniqueName->ignore($ignoreId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'provider' => ['required', 'string', 'max:255'],
            'campus' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'min_gpa' => ['nullable', 'numeric', 'min:0', 'max:9.99'],
            'min_semester' => ['nullable', 'integer', 'min:1', 'max:255'],
            'max_semester' => ['nullable', 'integer', 'min:1', 'max:255'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:255'],
            'requires_financial_need' => ['nullable', 'boolean'],
            'majors' => ['nullable', 'string'],
            'allowed_nationalities' => ['nullable', 'string'],
            'english_test_type' => ['nullable', 'string', 'max:255'],
            'min_english_score' => ['nullable', 'numeric', 'min:0', 'max:9999.9'],
            'scholarship_type' => ['nullable', Rule::in(['full', 'partial'])],
            'is_abroad' => ['required', 'boolean'],
            'other_requirements' => ['nullable', 'string'],
            'required_documents' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'open_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'official_url' => ['required', 'url', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:1000'],
            'last_verified' => ['required', 'date'],
            'year' => ['required', 'integer', 'min:2000', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Turn the comma/newline text fields into arrays.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function normalizeListFields(array $validated): array
    {
        foreach (self::LIST_FIELDS as $field) {
            $text = $validated[$field] ?? '';
            // Names can contain commas ("French history, language, and civilization"),
            // so split on new lines or semicolons when present; only a single plain
            // line is treated as a comma-separated list.
            $separator = preg_match('/[\r\n;]/', $text) ? '/[\r\n;]+/' : '/,/';
            $items = preg_split($separator, $text) ?: [];
            $validated[$field] = array_values(array_unique(array_filter(array_map('trim', $items))));
        }

        return $validated;
    }
}
