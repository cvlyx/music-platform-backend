<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\ArtistResource;
use App\Http\Resources\PlaylistResource;
use App\Http\Resources\SongResource;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2'],
            'type' => ['nullable', 'in:songs,artists,albums,playlists'],
        ]);

        $query = $validated['q'];
        $type = $validated['type'] ?? null;
        $results = [];

        if (! $type || $type === 'songs') {
            $results['songs'] = SongResource::collection(
                Song::where('title', 'LIKE', "%{$query}%")
                    ->with(['artist', 'album'])
                    ->orderByDesc('play_count')
                    ->limit(10)
                    ->get()
            );
        }

        if (! $type || $type === 'artists') {
            $results['artists'] = ArtistResource::collection(
                Artist::where('stage_name', 'LIKE', "%{$query}%")
                    ->with('user')
                    ->orderByDesc('monthly_listeners')
                    ->limit(10)
                    ->get()
            );
        }

        if (! $type || $type === 'albums') {
            $results['albums'] = AlbumResource::collection(
                Album::where('title', 'LIKE', "%{$query}%")
                    ->with('artist')
                    ->orderByDesc('release_date')
                    ->limit(10)
                    ->get()
            );
        }

        if (! $type || $type === 'playlists') {
            $results['playlists'] = PlaylistResource::collection(
                Playlist::where('name', 'LIKE', "%{$query}%")
                    ->where('is_public', true)
                    ->with('user')
                    ->limit(10)
                    ->get()
            );
        }

        return response()->json(['results' => $results]);
    }
}
