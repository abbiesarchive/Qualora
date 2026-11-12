<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $guarded = ['id', 'user_id'];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
            'gpa' => 'decimal:2',
            'age' => 'integer',
            'has_financial_need' => 'boolean',
            'graduation_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyPlans(): HasMany
    {
        return $this->hasMany(UserStudyPlan::class, 'profile_id');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(UserLanguage::class, 'profile_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(UserExperience::class, 'profile_id');
    }
}
