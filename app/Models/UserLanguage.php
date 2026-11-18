<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'language_name',
    'certificate_type',
    'certificate_score',
])]
class UserLanguage extends Model
{
    use HasUuids;

    protected $table = 'user_language';

    protected $primaryKey = 'user_language_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
