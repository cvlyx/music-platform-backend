<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'cover_image' => $this->cover_image,
            'release_date' => $this->release_date?->format('Y-m-d'),
            'type' => $this->type,
            'label' => $this->label,
            'artist' => new ArtistResource($this->whenLoaded('artist')),
            'songs' => SongResource::collection($this->whenLoaded('songs')),
            'songs_count' => $this->whenCounted('songs'),
            'created_at' => $this->created_at,
        ];
    }
}
