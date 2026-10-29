<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// user_id is deliberately not fillable: it is always set through $user->profile().
#[Fillable([
    'campus', 'major', 'semester', 'gpa', 'age', 'nationality', 'has_financial_need',
    'english_test_type', 'english_score',
    'achievements', 'organizations', 'goals', 'preferred_scholarship_type', 'open_to_abroad', 'circumstances',
])]
class Profile extends Model
{
    protected function casts(): array
    {
        return [
            'semester' => 'integer',
            'gpa' => 'decimal:2',
            'age' => 'integer',
            'has_financial_need' => 'boolean',
            'english_score' => 'decimal:1',
            'open_to_abroad' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
