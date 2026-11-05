<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Seeder;

/**
 * SAMPLE DATA!!!!!! fake and full AI for demo purposes
 * Replace them with real, verified scholarships (and real official_url / last_verified)
 * before the demo. Safe to re-run: rows are matched on name + year.
 */
class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) today()->format('Y');
        $docs = ['Transcript', 'Student ID', 'Motivation letter'];

        $rows = [
            [
                'name' => 'Sample Open Access Grant', 'provider' => 'Sample Foundation',
                'min_gpa' => 3.00, 'scholarship_type' => 'partial',
                'benefits' => 'Sample: partial tuition support for one year.',
                'required_documents' => $docs, 'deadline' => today()->addDays(45),
            ],
            [
                'name' => 'Sample Merit Excellence Award', 'provider' => 'Sample Merit Trust',
                'min_gpa' => 3.50, 'min_semester' => 3, 'max_semester' => 8, 'max_age' => 23,
                'scholarship_type' => 'full',
                'benefits' => 'Sample: full tuition plus monthly allowance.',
                'other_requirements' => 'Sample: active in at least one student organization.',
                'required_documents' => [...$docs, 'Recommendation letter'], 'deadline' => today()->addDays(20),
            ],
            [
                'name' => 'Sample Need-Based Support', 'provider' => 'Sample Community Fund',
                'min_gpa' => 2.75, 'requires_financial_need' => true, 'scholarship_type' => 'partial',
                'benefits' => 'Sample: living cost support per semester.',
                'required_documents' => [...$docs, 'Income statement'], 'deadline' => today()->addDays(6),
            ],
            [
                'name' => 'Sample Tech Majors Scholarship', 'provider' => 'Sample Tech Co.',
                'min_gpa' => 3.25, 'min_semester' => 4, 'majors' => ['Informatics', 'Information Systems'],
                'english_test_type' => 'IELTS', 'min_english_score' => 6.0, 'scholarship_type' => 'full',
                'benefits' => 'Sample: full tuition and internship placement.',
                'required_documents' => [...$docs, 'English test certificate'], 'deadline' => today()->addDays(60),
            ],
            [
                'name' => 'Sample Exchange Abroad Award', 'provider' => 'Sample Global Programme',
                'min_gpa' => 3.40, 'min_semester' => 5, 'max_semester' => 7,
                'english_test_type' => 'TOEFL', 'min_english_score' => 80,
                'is_abroad' => true, 'scholarship_type' => 'full',
                'benefits' => 'Sample: one semester abroad with travel and living costs.',
                'required_documents' => [...$docs, 'English test certificate', 'Passport copy'],
                'deadline' => today()->addDays(90),
            ],
        ];

        foreach ($rows as $row) {
            // forceCreate-style write: guarded fields (e.g. ai_*) are never set here.
            $scholarship = Scholarship::firstOrNew(['name' => $row['name'], 'year' => $year]);
            $scholarship->forceFill($row + [
                'level' => 'S1',
                'open_date' => today()->subDays(30),
                'official_url' => 'https://example.org/sample-scholarship',
                'last_verified' => today(),
                'is_active' => true,
            ])->save();
        }
    }
}
