<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    protected $fillable = [
        'artist_id',
        'title',
        'cover_image',
        'release_date',
        'type',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class)->orderBy('track_number');
    }
}
