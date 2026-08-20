<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaylistResource;
use App\Models\Playlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlaylistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $playlists = Playlist::with(['user', 'songs'])
            ->where('is_public', true)
            ->orderByDesc('follower_count')
            ->paginate($request->get('per_page', 20));

        return PlaylistResource::collection($playlists);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
        ]);

        $playlist = $request->user()->playlists()->create($validated);

        return response()->json(new PlaylistResource($playlist), 201);
    }

    public function show(Playlist $playlist): PlaylistResource
    {
        $playlist->load(['user', 'songs.artist', 'songs.album']);

        return new PlaylistResource($playlist);
    }

    public function update(Request $request, Playlist $playlist): JsonResponse
    {
        if ($playlist->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $playlist->update($validated);

        return response()->json(new PlaylistResource($playlist->fresh(['user', 'songs'])));
    }

    public function destroy(Request $request, Playlist $playlist): JsonResponse
    {
        if ($playlist->user_id !== $request->user()->id) {
            abort(403);
        }

        $playlist->delete();

        return response()->json(['message' => 'Playlist deleted successfully']);
    }

    public function addSong(Request $request, Playlist $playlist): JsonResponse
    {
        if ($playlist->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'song_id' => ['required', 'exists:songs,id'],
        ]);

        $maxPosition = $playlist->songs()->max('playlist_songs.position') ?? 0;

        $playlist->songs()->attach($validated['song_id'], [
            'position' => $maxPosition + 1,
        ]);

        return response()->json(['message' => 'Song added to playlist']);
    }

    public function removeSong(Request $request, Playlist $playlist, $songId): JsonResponse
    {
        if ($playlist->user_id !== $request->user()->id) {
            abort(403);
        }

        $playlist->songs()->detach($songId);

        return response()->json(['message' => 'Song removed from playlist']);
    }

    public function reorder(Request $request, Playlist $playlist): JsonResponse
    {
        if ($playlist->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'song_order' => ['required', 'array'],
            'song_order.*' => ['integer', 'exists:songs,id'],
        ]);

        foreach ($validated['song_order'] as $position => $songId) {
            $playlist->songs()->updateExistingPivot($songId, ['position' => $position + 1]);
        }

        return response()->json(['message' => 'Playlist reordered successfully']);
    }

    public function myPlaylists(Request $request): AnonymousResourceCollection
    {
        $playlists = $request->user()->playlists()
            ->with('songs')
            ->orderByDesc('updated_at')
            ->paginate($request->get('per_page', 20));

        return PlaylistResource::collection($playlists);
    }
}
