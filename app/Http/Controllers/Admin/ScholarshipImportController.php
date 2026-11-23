<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Services\ScholarshipExtractionService;
use App\Support\ScholarshipRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\View\View;

/**
 * Review step for AI-extracted scholarships. Nothing reaches the database until
 * an admin has edited the drafts and every row passes the normal admin rules.
 */
class ScholarshipImportController extends Controller
{
    public const SESSION_KEY = 'admin_import';

    public function create(Request $request): View
    {
        return view('admin.scholarships.import', [
            'import' => $request->session()->get(self::SESSION_KEY),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rows = array_slice((array) $request->input('items', []), 0, ScholarshipExtractionService::MAX_ITEMS, true);
        $rows = array_filter($rows, fn ($row) => is_array($row) && ! empty($row['include']));

        if ($rows === []) {
            return back()->withInput()->with('import_error', 'Tick at least one scholarship to save.');
        }

        $errors = new MessageBag;
        $clean = [];
        $seen = [];

        foreach ($rows as $i => $row) {
            $validator = Validator::make($row, ScholarshipRules::rules($row['year'] ?? null));

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    foreach ($messages as $message) {
                        $errors->add("items.$i.$field", $message);
                    }
                }

                continue;
            }

            $data = ScholarshipRules::normalizeListFields($validator->validated());
            $key = mb_strtolower($data['name']).'|'.$data['year'];

            if (isset($seen[$key])) {
                $errors->add("items.$i.name", 'The same name and year appears twice in this batch.');

                continue;
            }

            $seen[$key] = true;
            $clean[] = $data;
        }

        if ($errors->isNotEmpty()) {
            return back()->withInput()->withErrors($errors);
        }

        DB::transaction(fn () => array_map(fn (array $data) => Scholarship::create($data), $clean));

        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('admin.scholarships.index')
            ->with('status', count($clean).' scholarship(s) saved.');
    }

    public function discard(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('admin.scholarships.import');
    }
}
