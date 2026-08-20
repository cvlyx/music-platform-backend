<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\ArtistResource;
use App\Http\Resources\SongResource;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $trendingSongs = Song::with(['artist', 'album'])
            ->orderByDesc('play_count')
            ->limit(10)
            ->get();

        $topArtists = Artist::with('user')
            ->orderByDesc('monthly_listeners')
            ->limit(10)
            ->get();

        $newReleases = Album::with('artist')
            ->orderByDesc('release_date')
            ->limit(10)
            ->get();

        $recentlyPlayed = collect();

        if ($request->user()) {
            $recentlyPlayed = $request->user()->recentlyPlayed()
                ->with(['song.artist', 'song.album'])
                ->orderByDesc('played_at')
                ->limit(10)
                ->get()
                ->pluck('song');
        }

        return response()->json([
            'trending_songs' => SongResource::collection($trendingSongs),
            'top_artists' => ArtistResource::collection($topArtists),
            'new_releases' => AlbumResource::collection($newReleases),
            'recently_played' => SongResource::collection($recentlyPlayed),
        ]);
    }
}
