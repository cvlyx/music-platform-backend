<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    protected $fillable = [
        'user_id',
        'stage_name',
        'bio',
        'image',
        'banner_image',
        'verified',
        'monthly_listeners',
    ];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'monthly_listeners' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows')->withTimestamps();
    }
}
