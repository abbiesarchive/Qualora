<?php

namespace App\Http\Controllers\Ai;

use App\Exceptions\GeminiException;
use App\Http\Controllers\Admin\ScholarshipImportController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExtractScholarshipsRequest;
use App\Services\ScholarshipExtractionService;
use Illuminate\Http\RedirectResponse;

class ExtractScholarshipsController extends Controller
{
    /**
     * POST /admin/ai/extract-scholarships: admin only, runs only on the button click.
     * It never saves anything: the drafts wait in the session for admin review.
     */
    public function __invoke(ExtractScholarshipsRequest $request, ScholarshipExtractionService $service): RedirectResponse
    {
        $text = $request->validated('text');

        try {
            $result = $service->extract($text);
        } catch (GeminiException $e) {
            return redirect()->route('admin.scholarships.import')
                ->withInput(['text' => $text])
                ->with('ai_error', $e->getMessage());
        }

        $request->session()->put(ScholarshipImportController::SESSION_KEY, [
            'text' => $text,
            'items' => $result['items'],
            'skipped' => $result['skipped'],
        ]);

        return redirect()->route('admin.scholarships.import');
    }
}
