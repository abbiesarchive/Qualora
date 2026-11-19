<?php

namespace Tests\Unit;

use App\Http\Controllers\UserExperienceController;
use App\Http\Controllers\UserLanguageController;
use App\Models\UserStudyPlan;
use PHPUnit\Framework\TestCase;

/**
 * Keeps lang/id.json in step with the code: every literal __() / trans_choice() key
 * used by the app must have an Indonesian translation.
 */
class TranslationCoverageTest extends TestCase
{
    /** @return array<string, string> */
    private function translations(): array
    {
        return json_decode(file_get_contents(dirname(__DIR__, 2).'/lang/id.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return list<string> */
    private function usedKeys(): array
    {
        $root = dirname(__DIR__, 2);
        $files = [];
        foreach (['app', 'resources/views', 'routes'] as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("{$root}/{$dir}", \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (str_ends_with($file->getFilename(), '.php')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        $keys = [];
        $pattern = '/(?:\b__|\btrans_choice)\(\s*(?:\'((?:\\\\.|[^\'\\\\])*)\'|"((?:\\\\.|[^"\\\\])*)")/';
        foreach ($files as $file) {
            if (preg_match_all($pattern, file_get_contents($file), $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $key = ($m[1] ?? '') !== '' ? stripslashes($m[1]) : stripslashes($m[2] ?? '');
                    // "validation.required" style keys live in lang/id/*.php, not the JSON file.
                    if ($key !== '' && ! preg_match('/^[a-z_]+(\.[a-z_]+)+$/', $key)) {
                        $keys[] = $key;
                    }
                }
            }
        }

        return array_values(array_unique($keys));
    }

    public function test_every_literal_translation_key_has_an_indonesian_translation(): void
    {
        $missing = array_values(array_diff($this->usedKeys(), array_keys($this->translations())));

        $this->assertSame([], $missing, "Add these to lang/id.json:\n".implode("\n", $missing));
    }

    public function test_dropdown_and_data_labels_shown_through_variables_are_translated(): void
    {
        $labels = [
            ...UserStudyPlan::FIELDS,
            ...UserStudyPlan::LOCATIONS,
            ...UserLanguageController::LANGUAGES,
            ...UserExperienceController::EXPERIENCE_TYPES,
            // Dashboard "not added yet" chips and seeded scholarship labels.
            'GPA', 'Semester', 'Major', 'Age', 'Campus', 'Nationality', 'Financial need',
            'Fully Funded', 'Partial', 'Full', 'Government', 'Private',
            'Tuition', 'Living Allowance', 'Mentorship', 'Book Allowance', 'Accommodation', 'Travel',
        ];

        $missing = array_values(array_diff(array_unique($labels), array_keys($this->translations())));

        $this->assertSame([], $missing, "Add these to lang/id.json:\n".implode("\n", $missing));
    }

    public function test_translations_keep_the_same_placeholders_as_the_english_key(): void
    {
        $broken = [];
        foreach ($this->translations() as $key => $value) {
            if (str_contains($key, '|')) {
                continue; // plural forms are compared by trans_choice, not placeholder for placeholder
            }
            preg_match_all('/:[a-z_]+/', $key, $a);
            preg_match_all('/:[a-z_]+/', $value, $b);
            sort($a[0]);
            sort($b[0]);
            if ($a[0] !== $b[0]) {
                $broken[] = $key;
            }
        }

        $this->assertSame([], $broken, "Placeholders differ for:\n".implode("\n", $broken));
    }
}
