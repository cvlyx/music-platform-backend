<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'file_path' => $this->file_path,
            'cover_image' => $this->cover_image,
            'duration' => $this->duration,
            'duration_formatted' => gmdate('i:s', $this->duration),
            'track_number' => $this->track_number,
            'play_count' => $this->play_count,
            'is_explicit' => $this->is_explicit,
            'is_available' => $this->is_available,
            'artist' => new ArtistResource($this->whenLoaded('artist')),
            'album' => new AlbumResource($this->whenLoaded('album')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'is_liked' => $this->when(
                $request->user(),
                fn () => $request->user()->likedSongs()->where('song_id', $this->id)->exists()
            ),
            'created_at' => $this->created_at,
        ];
    }
}
