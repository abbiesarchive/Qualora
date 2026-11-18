<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'experience_type',
    'experience_name',
    'description',
])]
class UserExperience extends Model
{
    use HasUuids;

    protected $table = 'user_experiences';

    protected $primaryKey = 'user_experiences_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
