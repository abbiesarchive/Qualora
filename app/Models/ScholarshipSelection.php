<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipSelection extends Model
{
    protected $table = 'scholarship_selections';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'stage_order' => 'integer',
            'estimated_date' => 'date',
            'is_required' => 'boolean',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}

