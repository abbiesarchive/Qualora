<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipDocument extends Model
{
    protected $table = 'scholarship_documents';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}
