<?php

namespace App\Observers;

use App\Models\Scholarship;
use App\Services\RealtimeBroadcaster;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Tells open scholarship pages that the catalog changed (admin CRUD and the AI import).
 * Runs after commit so a rolled-back import never announces anything.
 */
class ScholarshipObserver implements ShouldHandleEventsAfterCommit
{
    /** Columns whose change students never see as a catalog change (AI cache written on a student's click). */
    private const IGNORED_COLUMNS = ['ai_explanation', 'ai_checklist', 'updated_at'];

    public function __construct(private RealtimeBroadcaster $broadcaster) {}

    public function created(Scholarship $scholarship): void
    {
        $this->broadcaster->scholarshipChanged($scholarship, 'created');
    }

    public function updated(Scholarship $scholarship): void
    {
        if (array_diff(array_keys($scholarship->getChanges()), self::IGNORED_COLUMNS) === []) {
            return;
        }

        $this->broadcaster->scholarshipChanged($scholarship, 'updated');
    }

    public function deleted(Scholarship $scholarship): void
    {
        $this->broadcaster->scholarshipChanged($scholarship, 'deleted');
    }
}
