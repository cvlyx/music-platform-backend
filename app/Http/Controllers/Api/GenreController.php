<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GenreController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $genres = Genre::withCount('songs')
            ->orderBy('name')
            ->paginate($request->get('per_page', 50));

        return GenreResource::collection($genres);
    }

    public function show(Genre $genre): GenreResource
    {
        $genre->loadCount('songs');

        return new GenreResource($genre);
    }
}
