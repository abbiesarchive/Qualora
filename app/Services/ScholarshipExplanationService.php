<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use App\Models\Scholarship;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Generates and caches a plain-language explanation + application checklist.
 *
 * Only stored scholarship data goes to the AI (never client-sent data, and nothing
 * about the student), so one cached result is safe to show to every student.
 * Whether a student qualifies is decided by EligibilityService, never here.
 */
class ScholarshipExplanationService
{
    private const NOT_STATED = 'not stated';

    private const SYSTEM = <<<'TXT'
You explain scholarships to Indonesian undergraduate students in a warm, encouraging, plain tone.
Use ONLY the facts in the provided scholarship data. If a fact is "not stated", say it is not stated.
Never invent requirements, amounts, dates or documents. Never promise or guarantee a result:
say "you may be eligible", never "you will get it". Do not decide whether the reader qualifies.
Remind the reader to verify details on the official site. Return only JSON matching the schema.
TXT;

    public function __construct(private GeminiService $gemini) {}

    /**
     * Return the cached explanation, generating and saving it when missing.
     *
     * @return array{explanation: string, checklist: list<string>}
     *
     * @throws GeminiException
     */
    public function explain(Scholarship $scholarship): array
    {
        if ($scholarship->ai_explanation && is_array($scholarship->ai_checklist)) {
            return ['explanation' => $scholarship->ai_explanation, 'checklist' => $scholarship->ai_checklist];
        }

        $scholarship->loadMissing('eligibility', 'studyRequirements', 'languageRequirements', 'benefitsList', 'documentsList');

        $raw = $this->gemini->generateJson(
            "Scholarship data (JSON):\n".json_encode($this->facts($scholarship), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            $this->schema(),
            self::SYSTEM,
        );

        $clean = $this->validated($raw);

        $scholarship->forceFill([
            'ai_explanation' => $clean['explanation'],
            'ai_checklist' => $clean['checklist'],
        ])->save();

        return $clean;
    }

    /**
     * Stored fields only. Anything empty becomes "not stated".
     *
     * @return array<string, mixed>
     */
    public function facts(Scholarship $s): array
    {
        $e = $s->eligibility;

        $documents = $s->documentsList->isNotEmpty()
            ? $s->documentsList->map(fn ($d) => $d->document_name.($d->is_required ? ' (required)' : ' (optional)'))->all()
            : ($s->required_documents ?: []);

        $benefits = $s->benefitsList->isNotEmpty()
            ? $s->benefitsList->map(fn ($b) => trim($b->benefit_name.' '.$b->benefit_description))->all()
            : [$s->benefits];

        return $this->orNotStated([
            'name' => $s->name,
            'provider' => $s->provider,
            'description' => $s->description,
            'type' => $s->scholarship_type,
            'abroad' => $s->is_abroad === null ? null : ($s->is_abroad ? 'yes' : 'no'),
            'campus' => $s->campus ?: 'any campus (national)',
            'min_gpa' => $s->min_gpa ?? $e?->min_gpa,
            'min_semester' => $s->min_semester ?? $e?->min_semester,
            'max_semester' => $s->max_semester ?? $e?->max_semester,
            'max_age' => $s->max_age ?? $e?->max_age,
            'requires_financial_need' => ($s->requires_financial_need ?? $e?->requires_financial_need) === null
                ? null : (($s->requires_financial_need ?? $e?->requires_financial_need) ? 'yes' : 'no'),
            'majors' => $s->majors ?: $s->studyRequirements->pluck('eligible_major')->filter()->all(),
            'allowed_nationalities' => $s->allowed_nationalities ?: ($e?->allowed_nationalities ?: []),
            'english_test' => $s->english_test_type ? trim($s->english_test_type.' '.$s->min_english_score) : null,
            'other_requirements' => $s->other_requirements,
            'benefits' => array_values(array_filter($benefits)),
            'required_documents' => array_values(array_filter($documents)),
            'open_date' => $s->open_date?->toDateString(),
            'deadline' => $s->deadline?->toDateString(),
            'last_verified' => $s->last_verified?->toDateString(),
            'official_url' => $s->official_url,
            'today' => Carbon::today()->toDateString(),
        ]);
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'explanation' => ['type' => 'string', 'description' => '3-6 sentences in plain English.'],
                'checklist' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Short, ordered application steps drawn only from the data.',
                ],
            ],
            'required' => ['explanation', 'checklist'],
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{explanation: string, checklist: list<string>}
     *
     * @throws GeminiException
     */
    private function validated(array $raw): array
    {
        $validator = Validator::make($raw, [
            'explanation' => ['required', 'string', 'min:20', 'max:2000'],
            'checklist' => ['required', 'array', 'min:1', 'max:15'],
            'checklist.*' => ['required', 'string', 'min:2', 'max:250'],
        ]);

        if ($validator->fails()) {
            Log::warning('AI explanation failed validation', ['errors' => $validator->errors()->all()]);
            throw new GeminiException(__('The AI gave an unexpected answer. Please try again.'));
        }

        $data = $validator->validated();

        return [
            'explanation' => trim($data['explanation']),
            'checklist' => array_values(array_map('trim', $data['checklist'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $facts
     * @return array<string, mixed>
     */
    private function orNotStated(array $facts): array
    {
        return array_map(
            fn ($v) => ($v === null || $v === '' || $v === []) ? self::NOT_STATED : $v,
            $facts,
        );
    }
}
