<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'desired_major',
    'study_field_category',
    'preferred_country',
    'preferred_university',
    'study_mode',
    'preferred_start_year',
    'preferred_duration',
])]
class UserStudyPlan extends Model
{
    use HasUuids;

    protected $table = 'user_study_plans';

    protected $primaryKey = 'user_study_plans_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'preferred_start_year' => 'integer',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
