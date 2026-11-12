<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStudyPlan extends Model
{
    protected $table = 'user_study_plans';
    protected $primaryKey = 'user_study_plans_id';
    public $timestamps = false; // in case table lacks created_at

    protected $guarded = [];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
}

