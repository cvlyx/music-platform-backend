<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\SongResource;
use App\Models\Album;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiscoverController extends Controller
{
    public function forYou(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $followedArtistIds = $user->followedArtists()->pluck('artists.id');
        $likedGenreIds = $user->likedSongs()->pluck('songs.id');

        $songs = Song::with(['artist', 'album', 'genres'])
            ->where(function ($q) use ($followedArtistIds) {
                $q->whereIn('artist_id', $followedArtistIds)
                    ->orWhereIn('id', function ($sub) use ($likedGenreIds) {
                        $sub->select('song_id')
                            ->from('song_genres')
                            ->whereIn('genre_id', $likedGenreIds);
                    });
            })
            ->orderByDesc('play_count')
            ->paginate($request->get('per_page', 20));

        return SongResource::collection($songs);
    }

    public function topCharts(Request $request): AnonymousResourceCollection
    {
        $songs = Song::with(['artist', 'album'])
            ->orderByDesc('play_count')
            ->paginate($request->get('per_page', 20));

        return SongResource::collection($songs);
    }

    public function newReleases(Request $request): AnonymousResourceCollection
    {
        $albums = Album::with(['artist', 'songs'])
            ->orderByDesc('release_date')
            ->paginate($request->get('per_page', 20));

        return AlbumResource::collection($albums);
    }
}
