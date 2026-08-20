<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentlyPlayed extends Model
{
    protected $fillable = ['user_id', 'song_id', 'played_at'];

    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
