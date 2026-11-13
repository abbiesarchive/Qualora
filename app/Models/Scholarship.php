<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Scholarship extends Model
{
    protected $guarded = ['id'];

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
            'interview_date' => 'date',
            'announcement_date' => 'date',
            'program_start_date' => 'date',
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

    public function applications(): HasMany
    {
        return $this->hasMany(UserApplication::class);
    }

    public function eligibility(): HasOne
    {
        return $this->hasOne(ScholarshipEligibility::class);
    }

    public function studyRequirements(): HasMany
    {
        return $this->hasMany(ScholarshipStudyRequirement::class);
    }

    public function languageRequirements(): HasMany
    {
        return $this->hasMany(ScholarshipLanguageRequirement::class);
    }

    public function benefitsList(): HasMany
    {
        return $this->hasMany(ScholarshipBenefit::class);
    }

    public function documentsList(): HasMany
    {
        return $this->hasMany(ScholarshipDocument::class);
    }

    public function selections(): HasMany
    {
        return $this->hasMany(ScholarshipSelection::class)->orderBy('stage_order');
    }

    public function getApplyUrlAttribute(): string
    {
        return $this->application_link ?: $this->official_url;
    }

    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (! $this->deadline) {
            return null;
        }

        return (int) today()->diffInDays($this->deadline, false);
    }

    public function getDaysUntilAnnouncementAttribute(): ?int
    {
        if (! $this->announcement_date) {
            return null;
        }

        return (int) today()->diffInDays($this->announcement_date, false);
    }
}
