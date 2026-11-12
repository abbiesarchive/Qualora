<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipEligibility extends Model
{
    protected $table = 'scholarship_eligibilities';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'min_gpa' => 'decimal:2',
            'min_semester' => 'integer',
            'max_semester' => 'integer',
            'min_age' => 'integer',
            'max_age' => 'integer',
            'allowed_nationalities' => 'array',
            'allowed_universities' => 'array',
            'requires_financial_need' => 'boolean',
            'requires_work_experience' => 'boolean',
            'min_experience_years' => 'decimal:1',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}

