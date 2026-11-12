<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLanguage extends Model
{
    protected $table = 'user_language';
    protected $primaryKey = 'user_language_id';
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'certificate_score' => 'decimal:1',
            'certificate_expiry' => 'date',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
}

