<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SongController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $songs = Song::with(['artist', 'album', 'genres'])
            ->when($request->query('artist_id'), fn ($q, $id) => $q->where('artist_id', $id))
            ->when($request->query('album_id'), fn ($q, $id) => $q->where('album_id', $id))
            ->when($request->query('genre_id'), fn ($q, $id) => $q->whereHas('genres', fn ($g) => $g->where('genres.id', $id)))
            ->orderByDesc('play_count')
            ->paginate($request->get('per_page', 20));

        return SongResource::collection($songs);
    }

    public function show(Song $song): SongResource
    {
        $song->load(['artist', 'album', 'genres']);

        return new SongResource($song);
    }

    public function play(Request $request, Song $song): JsonResponse
    {
        $song->incrementPlayCount();

        if ($request->user()) {
            $request->user()->recentlyPlayed()->create([
                'song_id' => $song->id,
                'played_at' => now(),
            ]);
        }

        return response()->json([
            'file_url' => $song->file_path,
            'duration' => $song->duration,
        ]);
    }

    public function toggleLike(Request $request, Song $song): JsonResponse
    {
        $user = $request->user();
        $isLiked = $user->likedSongs()->where('song_id', $song->id)->exists();

        if ($isLiked) {
            $user->likedSongs()->detach($song->id);
        } else {
            $user->likedSongs()->attach($song->id);
        }

        return response()->json(['liked' => ! $isLiked]);
    }

    public function likedSongs(Request $request): AnonymousResourceCollection
    {
        $songs = $request->user()->likedSongs()
            ->with(['artist', 'album', 'genres'])
            ->paginate($request->get('per_page', 20));

        return SongResource::collection($songs);
    }

    public function incrementPlayCount(Song $song): JsonResponse
    {
        $song->incrementPlayCount();

        return response()->json(['play_count' => $song->fresh()->play_count]);
    }
}
