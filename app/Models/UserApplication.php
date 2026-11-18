<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserApplication extends Model
{
    protected $table = 'user_applications';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'applied_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'preparing' => __('Preparing documents'),
            'submitted' => __('Submitted on the official website'),
            'interview' => __('Interview stage'),
            'accepted' => __('Accepted'),
            'rejected' => __('Not selected'),
            'withdrawn' => __('Withdrawn'),
            default => ucfirst($this->status),
        };
    }
}
