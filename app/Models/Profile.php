<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// user_id is deliberately not fillable: it is always set through $user->profile().
#[Fillable([
    'campus', 'major', 'semester', 'gpa', 'age', 'nationality', 'has_financial_need',
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
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
