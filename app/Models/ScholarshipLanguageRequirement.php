<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipLanguageRequirement extends Model
{
    protected $table = 'scholarship_language_requirements';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'minimum_score' => 'decimal:1',
            'validity_period_months' => 'integer',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}
