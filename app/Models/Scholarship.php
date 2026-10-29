<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ai_explanation / ai_checklist are not fillable: only the AI controller writes them, after validation.
#[Fillable([
    'name', 'provider', 'campus', 'level',
    'min_gpa', 'min_semester', 'max_semester', 'max_age', 'requires_financial_need', 'majors',
    'allowed_nationalities', 'english_test_type', 'min_english_score', 'scholarship_type', 'is_abroad',
    'other_requirements', 'required_documents', 'benefits',
    'open_date', 'deadline', 'official_url', 'last_verified', 'year', 'is_active',
])]
class Scholarship extends Model
{
    protected function casts(): array
    {
        return [
            'min_gpa' => 'decimal:2',
            'min_semester' => 'integer',
            'max_semester' => 'integer',
            'max_age' => 'integer',
            'requires_financial_need' => 'boolean',
            'majors' => 'array',
            'allowed_nationalities' => 'array',
            'min_english_score' => 'decimal:1',
            'is_abroad' => 'boolean',
            'required_documents' => 'array',
            'open_date' => 'date',
            'deadline' => 'date',
            'last_verified' => 'date',
            'year' => 'integer',
            'is_active' => 'boolean',
            'ai_checklist' => 'array',
        ];
    }

    public function savedBy(): HasMany
    {
        return $this->hasMany(SavedScholarship::class);
    }
}
