<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Genre extends Model
{
    protected $fillable = ['name', 'slug', 'image'];

    protected static function booted(): void
    {
        static::creating(function (Genre $genre) {
            if (empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });

        static::updating(function (Genre $genre) {
            if (empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'song_genres');
    }
}
