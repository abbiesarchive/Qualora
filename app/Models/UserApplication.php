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
            'preparing' => 'Persiapan Berkas',
            'submitted' => 'Sudah Submit di Web Resmi',
            'interview' => 'Tahap Wawancara',
            'accepted' => 'Lolos / Diterima',
            'rejected' => 'Belum Berhasil',
            'withdrawn' => 'Mengundurkan Diri',
            default => ucfirst($this->status),
        };
    }
}

