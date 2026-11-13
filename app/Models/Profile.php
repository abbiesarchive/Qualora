<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// user_id is deliberately not fillable: it is always set through $user->profile().
#[Fillable([
    'campus', 'major', 'semester', 'gpa', 'age', 'nationality', 'has_financial_need',
    'faculty', 'gender', 'city_of_residence', 'phone_number',
    'graduation_year', 'graduation_status', 'university_accreditation',
])]
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

    public function studyPlan(): HasOne
    {
        return $this->hasOne(UserStudyPlan::class, 'profile_id');
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
