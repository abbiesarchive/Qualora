<?php

namespace App\Http\Controllers\Ai;

use App\Exceptions\GeminiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExplainScholarshipRequest;
use App\Models\Scholarship;
use App\Services\ScholarshipExplanationService;
use Illuminate\Http\RedirectResponse;

class ExplainController extends Controller
{
    /** POST /ai/explain: runs only when the student clicks the button. */
    public function __invoke(ExplainScholarshipRequest $request, ScholarshipExplanationService $service): RedirectResponse
    {
        $scholarship = Scholarship::findOrFail($request->integer('scholarship_id'));
        $back = redirect()->to(route('scholarships.show', $scholarship).'#ai-explanation');

        try {
            $service->explain($scholarship);
        } catch (GeminiException $e) {
            return $back->with('ai_error', $e->getMessage());
        }

        return $back;
    }
}
