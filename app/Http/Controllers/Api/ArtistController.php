<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\ArtistResource;
use App\Http\Resources\SongResource;
use App\Http\Resources\UserResource;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArtistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $artists = Artist::with('user')
            ->orderByDesc('monthly_listeners')
            ->paginate($request->get('per_page', 20));

        return ArtistResource::collection($artists);
    }

    public function show(Artist $artist): ArtistResource
    {
        $artist->load(['user', 'songs', 'albums']);

        return new ArtistResource($artist);
    }

    public function songs(Artist $artist, Request $request): AnonymousResourceCollection
    {
        $songs = $artist->songs()
            ->with(['album', 'genres'])
            ->orderByDesc('play_count')
            ->paginate($request->get('per_page', 20));

        return SongResource::collection($songs);
    }

    public function albums(Artist $artist, Request $request): AnonymousResourceCollection
    {
        $albums = $artist->albums()
            ->with('songs')
            ->orderByDesc('release_date')
            ->paginate($request->get('per_page', 20));

        return AlbumResource::collection($albums);
    }

    public function toggleFollow(Request $request, Artist $artist): JsonResponse
    {
        $user = $request->user();
        $isFollowing = $user->followedArtists()->where('artist_id', $artist->id)->exists();

        if ($isFollowing) {
            $user->followedArtists()->detach($artist->id);
        } else {
            $user->followedArtists()->attach($artist->id);
        }

        return response()->json(['following' => ! $isFollowing]);
    }

    public function followers(Artist $artist, Request $request): AnonymousResourceCollection
    {
        $followers = $artist->followers()
            ->paginate($request->get('per_page', 20));

        return UserResource::collection($followers);
    }
}
