<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'username', 'email', 'password', 'avatar', 'role', 'country'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class);
    }

    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class);
    }

    public function likedSongs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'likes')->withTimestamps();
    }

    public function followedArtists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'follows')->withTimestamps();
    }

    public function recentlyPlayed(): HasMany
    {
        return $this->hasMany(RecentlyPlayed::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_premium' => 'boolean',
        ];
    }
}
