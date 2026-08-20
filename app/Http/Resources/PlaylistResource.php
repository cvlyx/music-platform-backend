<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'is_public' => $this->is_public,
            'follower_count' => $this->follower_count,
            'user' => new UserResource($this->whenLoaded('user')),
            'songs' => SongResource::collection($this->whenLoaded('songs')),
            'songs_count' => $this->whenCounted('songs'),
            'created_at' => $this->created_at,
        ];
    }
}
