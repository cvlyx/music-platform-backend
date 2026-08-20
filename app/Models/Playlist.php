<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover_image',
        'is_public',
        'follower_count',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'follower_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'playlist_songs')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
}
