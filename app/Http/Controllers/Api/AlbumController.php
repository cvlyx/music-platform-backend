<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\Http\Resources\SongResource;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlbumController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $albums = Album::with(['artist', 'songs'])
            ->when($request->query('artist_id'), fn ($q, $id) => $q->where('artist_id', $id))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderByDesc('release_date')
            ->paginate($request->get('per_page', 20));

        return AlbumResource::collection($albums);
    }

    public function show(Album $album): AlbumResource
    {
        $album->load(['artist', 'songs.genres']);

        return new AlbumResource($album);
    }

    public function songs(Album $album, Request $request): AnonymousResourceCollection
    {
        $songs = $album->songs()
            ->with(['artist', 'genres'])
            ->orderBy('track_number')
            ->paginate($request->get('per_page', 20));

        return SongResource::collection($songs);
    }
}
