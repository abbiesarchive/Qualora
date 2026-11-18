<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Light "what are you looking for" preferences. Used only to help order and
 * suggest scholarships; never to pass or fail an eligibility rule.
 */
#[Fillable([
    'study_field_category',
    'preferred_country',
])]
class UserStudyPlan extends Model
{
    use HasUuids;

    public const FIELDS = [
        'Technology & Computing',
        'Engineering',
        'Business & Economics',
        'Health & Medicine',
        'Science & Mathematics',
        'Social Sciences & Law',
        'Arts, Design & Humanities',
        'Education',
        'Other',
    ];

    public const LOCATIONS = [
        'Indonesia',
        'Asia (outside Indonesia)',
        'Europe',
        'North America',
        'Oceania',
        'Open to anywhere',
    ];

    protected $table = 'user_study_plans';

    protected $primaryKey = 'user_study_plans_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
