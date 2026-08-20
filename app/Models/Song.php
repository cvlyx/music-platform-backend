<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    protected $fillable = [
        'artist_id',
        'album_id',
        'title',
        'file_path',
        'cover_image',
        'duration',
        'track_number',
        'play_count',
        'is_explicit',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'track_number' => 'integer',
            'play_count' => 'integer',
            'is_explicit' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'song_genres')->withTimestamps();
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_songs')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function recentlyPlayedBy(): HasMany
    {
        return $this->hasMany(RecentlyPlayed::class);
    }

    public function incrementPlayCount(): void
    {
        $this->increment('play_count');
    }
}
