<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\ArtistManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SongController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/home', [HomeController::class, 'index']);
Route::get('/discover/top-charts', [DiscoverController::class, 'topCharts']);
Route::get('/discover/new-releases', [DiscoverController::class, 'newReleases']);
Route::get('/search', [SearchController::class, 'search']);

Route::get('/songs', [SongController::class, 'index']);
Route::get('/songs/{song}', [SongController::class, 'show']);
Route::post('/songs/{song}/play', [SongController::class, 'play']);
Route::post('/songs/{song}/increment-play', [SongController::class, 'incrementPlayCount']);

Route::get('/albums', [AlbumController::class, 'index']);
Route::get('/albums/{album}', [AlbumController::class, 'show']);
Route::get('/albums/{album}/songs', [AlbumController::class, 'songs']);

Route::get('/artists', [ArtistController::class, 'index']);
Route::get('/artists/{artist}', [ArtistController::class, 'show']);
Route::get('/artists/{artist}/songs', [ArtistController::class, 'songs']);
Route::get('/artists/{artist}/albums', [ArtistController::class, 'albums']);
Route::get('/artists/{artist}/followers', [ArtistController::class, 'followers']);

Route::get('/genres', [GenreController::class, 'index']);
Route::get('/genres/{genre}', [GenreController::class, 'show']);

Route::get('/playlists', [PlaylistController::class, 'index']);
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);

Route::get('/plans', [PremiumController::class, 'plans']);

// PayChangu webhook (no auth)
Route::post('/premium/callback', [PremiumController::class, 'callback']);
Route::post('/webhooks/paychangu', [PremiumController::class, 'webhook']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'changePassword']);
    Route::get('/auth/user', fn (Request $request) => $request->user());

    // Discover
    Route::get('/discover/for-you', [DiscoverController::class, 'forYou']);

    // Songs
    Route::post('/songs/{song}/like', [SongController::class, 'toggleLike']);
    Route::get('/me/liked-songs', [SongController::class, 'likedSongs']);

    // Artists
    Route::post('/artists/{artist}/follow', [ArtistController::class, 'toggleFollow']);

    // Playlists
    Route::post('/playlists', [PlaylistController::class, 'store']);
    Route::put('/playlists/{playlist}', [PlaylistController::class, 'update']);
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'destroy']);
    Route::post('/playlists/{playlist}/songs', [PlaylistController::class, 'addSong']);
    Route::delete('/playlists/{playlist}/songs/{songId}', [PlaylistController::class, 'removeSong']);
    Route::put('/playlists/{playlist}/reorder', [PlaylistController::class, 'reorder']);
    Route::get('/me/playlists', [PlaylistController::class, 'myPlaylists']);

    // Premium
    Route::get('/premium/status', [PremiumController::class, 'status']);
    Route::post('/premium/subscribe', [PremiumController::class, 'subscribe']);
    Route::post('/premium/cancel', [PremiumController::class, 'cancel']);
    Route::get('/premium/payments', [PremiumController::class, 'paymentHistory']);

    // File uploads
    Route::post('/upload/song', [FileUploadController::class, 'uploadAudio']);
    Route::post('/upload/image', [FileUploadController::class, 'uploadImage']);

    // Artist management
    Route::post('/me/become-artist', [ArtistManagementController::class, 'becomeArtist']);
    Route::get('/me/artist-profile', [ArtistManagementController::class, 'myProfile']);
    Route::put('/me/artist-profile', [ArtistManagementController::class, 'updateProfile']);
    Route::get('/me/songs', [ArtistManagementController::class, 'mySongs']);
    Route::get('/me/albums', [ArtistManagementController::class, 'myAlbums']);
    Route::post('/me/songs', [ArtistManagementController::class, 'uploadSong']);
    Route::put('/me/songs/{song}', [ArtistManagementController::class, 'updateSong']);
    Route::delete('/me/songs/{song}', [ArtistManagementController::class, 'deleteSong']);
    Route::post('/me/albums', [ArtistManagementController::class, 'createAlbum']);
    Route::put('/me/albums/{album}', [ArtistManagementController::class, 'updateAlbum']);
    Route::delete('/me/albums/{album}', [ArtistManagementController::class, 'deleteAlbum']);
    Route::get('/me/artist-stats', [ArtistManagementController::class, 'analytics']);

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{user}/ban', [AdminController::class, 'banUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        Route::get('/songs', [AdminController::class, 'songs']);
        Route::delete('/songs/{song}', [AdminController::class, 'deleteSong']);
        Route::get('/artists', [AdminController::class, 'artists']);
        Route::put('/artists/{artist}/verify', [AdminController::class, 'verifyArtist']);
        Route::get('/albums', [AdminController::class, 'albums']);
        Route::delete('/albums/{album}', [AdminController::class, 'deleteAlbum']);
    });
});
