<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Payment;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! $request->user() || $request->user()->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'total_artists' => Artist::count(),
            'total_songs' => Song::count(),
            'total_albums' => Album::count(),
            'total_plays' => (int) Song::sum('play_count'),
            'premium_users' => User::where('is_premium', true)->count(),
            'total_revenue' => (float) Payment::where('status', 'completed')->sum('amount'),
            'recent_users' => User::latest()->take(5)->get(['id', 'name', 'username', 'email', 'role', 'created_at']),
            'recent_payments' => Payment::with('user:id,name,username')->latest()->take(10)->get(),
        ]);
    }

    public function users(Request $request): AnonymousResourceCollection
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 20));

        return \App\Http\Resources\UserResource::collection($users);
    }

    public function banUser(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot ban an admin user'], 400);
        }

        $user->update([
            'banned_at' => $user->banned_at ? null : now(),
        ]);

        return response()->json([
            'message' => $user->banned_at ? 'User banned' : 'User unbanned',
            'banned' => (bool) $user->banned_at,
        ]);
    }

    public function deleteUser(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete an admin user'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function songs(Request $request): AnonymousResourceCollection
    {
        $query = Song::with(['artist', 'album', 'genres']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        return \App\Http\Resources\SongResource::collection(
            $query->latest()->paginate($request->get('per_page', 20))
        );
    }

    public function deleteSong(Song $song): JsonResponse
    {
        $song->delete();
        return response()->json(['message' => 'Song deleted successfully']);
    }

    public function artists(Request $request): AnonymousResourceCollection
    {
        $query = Artist::with(['user', 'songs', 'albums']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('stage_name', 'like', "%{$search}%");
        }

        return \App\Http\Resources\ArtistResource::collection(
            $query->latest()->paginate($request->get('per_page', 20))
        );
    }

    public function verifyArtist(Artist $artist): JsonResponse
    {
        $artist->update(['verified' => ! $artist->verified]);

        return response()->json([
            'message' => $artist->verified ? 'Artist verified' : 'Artist verification removed',
            'verified' => $artist->verified,
        ]);
    }

    public function albums(Request $request): AnonymousResourceCollection
    {
        $query = Album::with(['artist', 'songs']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        return \App\Http\Resources\AlbumResource::collection(
            $query->latest()->paginate($request->get('per_page', 20))
        );
    }

    public function deleteAlbum(Album $album): JsonResponse
    {
        $album->delete();
        return response()->json(['message' => 'Album deleted successfully']);
    }
}
