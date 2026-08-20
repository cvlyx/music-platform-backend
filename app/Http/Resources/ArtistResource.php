<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stage_name' => $this->stage_name,
            'bio' => $this->bio,
            'image' => $this->image,
            'banner_image' => $this->banner_image,
            'verified' => $this->verified,
            'monthly_listeners' => $this->monthly_listeners,
            'user' => new UserResource($this->whenLoaded('user')),
            'songs_count' => $this->whenCounted('songs'),
            'albums_count' => $this->whenCounted('albums'),
            'followers_count' => $this->whenCounted('followers'),
            'is_following' => $this->when(
                $request->user(),
                fn () => $request->user()->followedArtists()->where('artist_id', $this->id)->exists()
            ),
            'created_at' => $this->created_at,
        ];
    }
}
