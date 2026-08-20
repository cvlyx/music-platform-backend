<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\SongResource;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Http;

class ArtistManagementController extends Controller
{
    public function becomeArtist(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->artist) {
            return response()->json(['message' => 'You are already an artist'], 400);
        }

        $validated = $request->validate([
            'stage_name' => ['required', 'string', 'max:255', 'unique:artists,stage_name'],
            'bio' => ['sometimes', 'nullable', 'string'],
        ]);

        $artist = Artist::create([
            'user_id' => $user->id,
            'stage_name' => $validated['stage_name'],
            'bio' => $validated['bio'] ?? null,
        ]);

        $user->update(['role' => 'artist']);

        return response()->json([
            'message' => 'Artist profile created successfully',
            'artist' => $artist,
        ], 201);
    }

    public function myProfile(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'You are not an artist yet'], 404);
        }

        $artist->load(['songs', 'albums', 'followers']);

        return response()->json([
            'artist' => $artist,
            'stats' => [
                'total_songs' => $artist->songs()->count(),
                'total_albums' => $artist->albums()->count(),
                'total_plays' => (int) $artist->songs()->sum('play_count'),
                'total_followers' => $artist->followers()->count(),
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'You are not an artist yet'], 404);
        }

        $validated = $request->validate([
            'stage_name' => ['sometimes', 'string', 'max:255', 'unique:artists,stage_name,'.$artist->id],
            'bio' => ['sometimes', 'nullable', 'string'],
            'image' => ['sometimes', 'nullable', 'string'],
            'banner_image' => ['sometimes', 'nullable', 'string'],
        ]);

        $artist->update($validated);

        return response()->json(['artist' => $artist->fresh()]);
    }

    public function uploadSong(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'You are not an artist yet'], 404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string', 'max:500'],
            'cover_image' => ['sometimes', 'nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'album_id' => ['sometimes', 'nullable', 'exists:albums,id'],
            'track_number' => ['sometimes', 'nullable', 'integer'],
            'is_explicit' => ['sometimes', 'boolean'],
            'genre_ids' => ['sometimes', 'array'],
            'genre_ids.*' => ['exists:genres,id'],
        ]);

        $song = Song::create([
            'artist_id' => $artist->id,
            'title' => $validated['title'],
            'file_path' => $validated['file_path'],
            'cover_image' => $validated['cover_image'] ?? null,
            'duration' => $validated['duration'],
            'album_id' => $validated['album_id'] ?? null,
            'track_number' => $validated['track_number'] ?? null,
            'is_explicit' => $validated['is_explicit'] ?? false,
        ]);

        if (! empty($validated['genre_ids'])) {
            $song->genres()->sync($validated['genre_ids']);
        }

        $song->load(['artist', 'album', 'genres']);

        return response()->json([
            'message' => 'Song uploaded successfully',
            'song' => $song,
        ], 201);
    }

    public function updateSong(Request $request, Song $song): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist || $song->artist_id !== $artist->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'cover_image' => ['sometimes', 'nullable', 'string'],
            'album_id' => ['sometimes', 'nullable', 'exists:albums,id'],
            'track_number' => ['sometimes', 'nullable', 'integer'],
            'is_explicit' => ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'genre_ids' => ['sometimes', 'array'],
            'genre_ids.*' => ['exists:genres,id'],
        ]);

        $genreIds = $validated['genre_ids'] ?? null;
        unset($validated['genre_ids']);

        $song->update($validated);

        if ($genreIds !== null) {
            $song->genres()->sync($genreIds);
        }

        $song->load(['artist', 'album', 'genres']);

        return response()->json(['song' => $song]);
    }

    public function deleteSong(Request $request, Song $song): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist || $song->artist_id !== $artist->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $song->delete();

        return response()->json(['message' => 'Song deleted successfully']);
    }

    public function createAlbum(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'You are not an artist yet'], 404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'cover_image' => ['sometimes', 'nullable', 'string'],
            'release_date' => ['sometimes', 'nullable', 'date'],
            'type' => ['sometimes', 'string', 'in:album,single,ep,compilation'],
            'label' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => $validated['title'],
            'cover_image' => $validated['cover_image'] ?? null,
            'release_date' => $validated['release_date'] ?? now()->toDateString(),
            'type' => $validated['type'] ?? 'album',
            'label' => $validated['label'] ?? null,
        ]);

        return response()->json([
            'message' => 'Album created successfully',
            'album' => $album,
        ], 201);
    }

    public function updateAlbum(Request $request, Album $album): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist || $album->artist_id !== $artist->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'cover_image' => ['sometimes', 'nullable', 'string'],
            'release_date' => ['sometimes', 'nullable', 'date'],
            'type' => ['sometimes', 'string', 'in:album,single,ep,compilation'],
            'label' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $album->update($validated);

        return response()->json(['album' => $album->fresh()]);
    }

    public function deleteAlbum(Request $request, Album $album): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist || $album->artist_id !== $artist->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $album->delete();

        return response()->json(['message' => 'Album deleted successfully']);
    }

    public function analytics(Request $request): JsonResponse
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'You are not an artist yet'], 404);
        }

        $totalPlays = (int) $artist->songs()->sum('play_count');
        $totalFollowers = $artist->followers()->count();
        $topSongs = $artist->songs()
            ->orderByDesc('play_count')
            ->take(10)
            ->get(['id', 'title', 'play_count', 'cover_image']);
        $monthlyListeners = $artist->monthly_listeners;

        $recentPayments = $artist->user->payments()
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'total_plays' => $totalPlays,
            'total_followers' => $totalFollowers,
            'monthly_listeners' => $monthlyListeners,
            'total_songs' => $artist->songs()->count(),
            'total_albums' => $artist->albums()->count(),
            'top_songs' => $topSongs,
            'recent_payments' => $recentPayments,
        ]);
    }

    public function mySongs(Request $request): AnonymousResourceCollection
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'You are not an artist yet'], 404);
        }

        $songs = $artist->songs()
            ->with(['album', 'genres'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return SongResource::collection($songs);
    }

    public function myAlbums(Request $request): AnonymousResourceCollection
    {
        $artist = $request->user()->artist;

        if (! $artist) {
            return response()->json(['message' => 'You are not an artist yet'], 404);
        }

        $albums = $artist->albums()
            ->with('songs')
            ->latest()
            ->paginate($request->get('per_page', 20));

        return AlbumResource::collection($albums);
    }
}
