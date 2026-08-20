# Music Platform Backend - API Documentation

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Database Schema](#database-schema)
- [Models and Relationships](#models-and-relationships)
- [API Response Format](#api-response-format)
- [Frontend Integration Guide](#frontend-integration-guide)
- [Limitations and Known Issues](#limitations-and-known-issues)
- [Future Improvements](#future-improvements)

---

## Overview

RESTful API backend for a full-featured music streaming platform. Supports user authentication, music browsing, playlists, likes, follows, search, and personalized discovery.

**Base URL:** `http://localhost:8000/api`

---

## Tech Stack

| Component     | Technology                |
|---------------|---------------------------|
| Framework     | Laravel 13                |
| PHP           | 8.3+                      |
| Auth          | Laravel Sanctum (tokens)  |
| Database      | SQLite (switchable)       |
| ORM           | Eloquent                  |
| Code Style    | Laravel Pint              |

---

## Getting Started

### Prerequisites
- PHP 8.3 or higher
- Composer
- SQLite (default) or MySQL

### Installation

```bash
cd music-platform-backend

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed    # optional - loads sample data
php artisan serve
```

API available at `http://localhost:8000/api`

### Default Test Accounts

| Email                      | Password  | Role   |
|----------------------------|-----------|--------|
| admin@musicplatform.com    | password  | admin  |
| user1@example.com          | password  | user   |
| user2@example.com          | password  | user   |
| theneonwaves@artist.com    | password  | artist |

---

## Authentication

Uses **Laravel Sanctum** personal access tokens.

1. Register or Login to receive a token
2. Send token in `Authorization` header on protected routes
3. Logout to revoke the current token

### Header Format

```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Token Lifecycle

- Created on `POST /auth/register` and `POST /auth/login`
- Destroyed on `POST /auth/logout`
- Each user can have multiple active tokens (one per device)
- Tokens do not expire by default

---

## API Endpoints

### Legend

- `[AUTH]` = Requires `Authorization: Bearer <token>` header
- `[PUBLIC]` = No token needed

---

### Auth

#### POST /api/auth/register [PUBLIC]

**Body:**
```json
{
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "secret123",
    "password_confirmation": "secret123"
}
```

**Response 201:**
```json
{
    "user": {
        "id": 15,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "avatar": null,
        "role": "user",
        "country": null,
        "is_premium": false,
        "created_at": "2026-08-20T18:00:00.000000Z"
    },
    "token": "1|abc123def456..."
}
```

---

#### POST /api/auth/login [PUBLIC]

**Body:**
```json
{
    "email": "john@example.com",
    "password": "secret123"
}
```

**Response 200:**
```json
{
    "user": { "id": 15, "name": "John Doe", "token": "..." },
    "token": "2|abc123def456..."
}
```

**Error 422:**
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": { "email": ["The provided credentials are incorrect."] }
}
```

---

#### POST /api/auth/logout [AUTH]

Revokes the current access token.

**Response 200:** `{ "message": "Logged out successfully" }`

---

#### GET /api/auth/user [AUTH]

Returns the authenticated user object.

---

#### PUT /api/auth/profile [AUTH]

**Body (all optional):**
```json
{
    "name": "New Name",
    "username": "newusername",
    "avatar": "https://example.com/avatar.jpg",
    "country": "US"
}
```

---

#### PUT /api/auth/password [AUTH]

**Body:**
```json
{
    "current_password": "oldpassword",
    "password": "newpassword",
    "password_confirmation": "newpassword"
}
```

---

### Home / Discovery

#### GET /api/home [PUBLIC]

**Response 200:**
```json
{
    "trending_songs": [ "SongResource[] - top 10 by play_count" ],
    "top_artists": [ "ArtistResource[] - top 10 by monthly_listeners" ],
    "new_releases": [ "AlbumResource[] - latest 10 albums" ],
    "recently_played": [ "SongResource[] - if authenticated, user's recent 10" ]
}
```

---

#### GET /api/discover/for-you [AUTH]

Returns songs from artists the user follows or genres of liked songs.
Supports `?per_page=N` (default 20).

---

#### GET /api/discover/top-charts [PUBLIC]

All songs sorted by play count descending.
Supports `?per_page=N`.

---

#### GET /api/discover/new-releases [PUBLIC]

All albums sorted by release date descending.
Supports `?per_page=N`.

---

### Songs

#### GET /api/songs [PUBLIC]

**Query params (all optional):**
- `artist_id` - Filter by artist
- `album_id` - Filter by album
- `genre_id` - Filter by genre
- `per_page` - Results per page (default 20)
- `page` - Page number

**Response 200:**
```json
{
    "data": [
        {
            "id": 42,
            "title": "Smooth Operator",
            "file_path": "songs/Smooth Operator.mp3",
            "cover_image": null,
            "duration": 357,
            "duration_formatted": "05:57",
            "track_number": 2,
            "play_count": 491692,
            "is_explicit": false,
            "is_available": true,
            "artist": { "id": 1, "stage_name": "The Neon Waves", ... },
            "album": { "id": 9, "title": "After Hours", ... },
            "genres": [ { "id": 1, "name": "Pop", "slug": "pop" } ],
            "is_liked": true,
            "created_at": "2026-08-20T17:57:32.000000Z"
        }
    ],
    "links": { "first": "...", "last": "...", "next": "...", "prev": "..." },
    "meta": { "current_page": 1, "last_page": 3, "per_page": 20, "total": 45 }
}
```

---

#### GET /api/songs/{id} [PUBLIC]

Returns full song details with artist, album, and genres loaded.

---

#### POST /api/songs/{id}/play [AUTH]

Increments play count AND records in recently_played for the user.

**Response 200:**
```json
{
    "file_url": "http://localhost:8000/storage/songs/Smooth Operator.mp3",
    "duration": 357
}
```

---

#### POST /api/songs/{id}/like [AUTH]

Toggle like/unlike on a song.

**Response 200:** `{ "liked": true }` or `{ "liked": false }`

---

#### GET /api/me/liked-songs [AUTH]

Returns paginated liked songs for the authenticated user.
Supports `?per_page=N`.

---

#### POST /api/songs/{id}/increment-play [PUBLIC]

Increments play count without recording user history.

---

### Albums

#### GET /api/albums [PUBLIC]

**Query params:**
- `artist_id` - Filter by artist
- `type` - Filter by type: album, single, ep, compilation
- `per_page`, `page`

Sorted by release date descending.

---

#### GET /api/albums/{id} [PUBLIC]

Returns album with artist and songs (with genres) loaded.

---

#### GET /api/albums/{id}/songs [PUBLIC]

Returns songs in track number order.
Supports `?per_page=N`.

---

### Artists

#### GET /api/artists [PUBLIC]

Sorted by monthly listeners descending.
Supports `?per_page=N`.

---

#### GET /api/artists/{id} [PUBLIC]

**Response 200:**
```json
{
    "id": 1,
    "stage_name": "The Neon Waves",
    "bio": "Electronic synth-pop duo from Berlin.",
    "image": null,
    "banner_image": null,
    "verified": true,
    "monthly_listeners": 5000000,
    "user": { "id": 10, "name": "The Neon Waves", ... },
    "songs_count": 5,
    "albums_count": 2,
    "followers_count": 3,
    "is_following": true,
    "created_at": "..."
}
```

---

#### GET /api/artists/{id}/songs [PUBLIC]

Supports `?per_page=N`. Sorted by play count.

---

#### GET /api/artists/{id}/albums [PUBLIC]

Supports `?per_page=N`. Sorted by release date.

---

#### POST /api/artists/{id}/follow [AUTH]

Toggle follow/unfollow on an artist.

**Response 200:** `{ "following": true }` or `{ "following": false }`

---

#### GET /api/artists/{id}/followers [PUBLIC]

Returns paginated list of users following this artist.

---

### Playlists

#### GET /api/playlists [PUBLIC]

Returns public playlists sorted by follower count.

---

#### GET /api/playlists/{id} [PUBLIC]

Returns playlist with user and songs (with artist and album) loaded.

---

#### POST /api/playlists [AUTH]

**Body:**
```json
{
    "name": "My Road Trip Mix",
    "description": "Songs for the open road",
    "is_public": true
}
```

**Response 201:** PlaylistResource.

---

#### PUT /api/playlists/{id} [AUTH]

**Body (all optional):** `name`, `description`, `is_public`

Returns 403 if you don't own the playlist.

---

#### DELETE /api/playlists/{id} [AUTH]

Returns 403 if you don't own the playlist.

---

#### POST /api/playlists/{id}/songs [AUTH]

**Body:** `{ "song_id": 42 }`

---

#### DELETE /api/playlists/{playlist_id}/songs/{song_id} [AUTH]

Removes a song from the playlist.

---

#### PUT /api/playlists/{id}/reorder [AUTH]

**Body:** Array of song IDs in desired order.
```json
{ "song_order": [42, 15, 7, 23] }
```

---

#### GET /api/me/playlists [AUTH]

Returns all playlists (public and private) owned by the current user.

---

### Genres

#### GET /api/genres [PUBLIC]

Returns genres with song counts.
Supports `?per_page=N` (default 50).

---

#### GET /api/genres/{slug} [PUBLIC]

Returns genre details with song count.

---

### Search

#### GET /api/search [PUBLIC]

**Params:**
- `q` (required, min 2 chars) - Search query
- `type` (optional) - `songs`, `artists`, `albums`, or `playlists`

If `type` is omitted, searches all types and returns them grouped.

**Response 200:**
```json
{
    "results": {
        "songs": [ ... up to 10 results ... ],
        "artists": [ ... up to 10 results ... ],
        "albums": [ ... up to 10 results ... ],
        "playlists": [ ... up to 10 results ... ]
    }
}
```

---

## Database Schema

### users
```
id              bigint PK
name            string
username        string unique
email           string unique
email_verified_at  timestamp nullable
password        string
avatar          string nullable
role            enum: user, artist, admin
country         string nullable
last_login_at   timestamp nullable
is_premium      boolean default false
timestamps
```

### artists
```
id              bigint PK
user_id         bigint FK -> users
stage_name      string
bio             text nullable
image           string nullable
banner_image    string nullable
verified        boolean default false
monthly_listeners  unsigned big int default 0
timestamps
```

### genres
```
id              bigint PK
name            string unique
slug            string unique
image           string nullable
timestamps
```

### albums
```
id              bigint PK
artist_id       bigint FK -> artists
title           string
cover_image     string nullable
release_date    date nullable
type            enum: album, single, ep, compilation
label           string nullable
timestamps
```

### songs
```
id              bigint PK
artist_id       bigint FK -> artists
album_id        bigint FK -> albums nullable
title           string indexed
file_path       string
cover_image     string nullable
duration        int (seconds)
track_number    unsigned int nullable
play_count      unsigned big int default 0
is_explicit     boolean default false
is_available    boolean default true
timestamps
```

### song_genres (pivot)
```
id              bigint PK
song_id         bigint FK -> songs
genre_id        bigint FK -> genres
timestamps
UNIQUE(song_id, genre_id)
```

### playlists
```
id              bigint PK
user_id         bigint FK -> users
name            string
description     text nullable
cover_image     string nullable
is_public       boolean default true
follower_count  unsigned int default 0
timestamps
```

### playlist_songs (pivot)
```
id              bigint PK
playlist_id     bigint FK -> playlists
song_id         bigint FK -> songs
position        unsigned int default 0
timestamps
UNIQUE(playlist_id, song_id)
```

### likes (pivot)
```
id              bigint PK
user_id         bigint FK -> users
song_id         bigint FK -> songs
timestamps
UNIQUE(user_id, song_id)
```

### follows (pivot)
```
id              bigint PK
user_id         bigint FK -> users
artist_id       bigint FK -> artists
timestamps
UNIQUE(user_id, artist_id)
```

### recently_played
```
id              bigint PK
user_id         bigint FK -> users
song_id         bigint FK -> songs
played_at       timestamp
timestamps
INDEX(user_id, played_at)
```

### user_subscriptions
```
id              bigint PK
user_id         bigint FK -> users
plan            enum: free, premium, family
starts_at       timestamp
expires_at      timestamp nullable
is_active       boolean default true
timestamps
```

---

## Models and Relationships

```
User
  hasOne       -> Artist
  hasMany      -> Playlists
  belongsToMany -> Songs (via likes table)       -> likedSongs
  belongsToMany -> Artists (via follows table)   -> followedArtists
  hasMany      -> RecentlyPlayed
  hasOne       -> UserSubscription

Artist
  belongsTo    -> User
  hasMany      -> Albums
  hasMany      -> Songs
  belongsToMany -> Users (via follows table)     -> followers

Song
  belongsTo    -> Artist
  belongsTo    -> Album
  belongsToMany -> Genres (via song_genres)
  belongsToMany -> Users (via likes table)       -> likedByUsers
  belongsToMany -> Playlists (via playlist_songs)
  hasMany      -> RecentlyPlayed

Album
  belongsTo    -> Artist
  hasMany      -> Songs

Genre
  hasMany      -> Songs (via song_genres)

Playlist
  belongsTo    -> User
  belongsToMany -> Songs (via playlist_songs, ordered by position)
```

---

## API Response Format

### Paginated Collections

All list endpoints return:
```json
{
    "data": [ ... ],
    "links": { "first": "...", "last": "...", "next": "...", "prev": "..." },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "path": "http://localhost:8000/api/songs",
        "per_page": 20,
        "to": 20,
        "total": 45
    }
}
```

### Errors

**422 Validation:**
```json
{ "message": "...", "errors": { "field": ["error message"] } }
```

**401 Unauthorized:** `{ "message": "Unauthenticated." }`
**403 Forbidden:** `{ "message": "This action is unauthorized." }`
**404 Not Found:** `{ "message": "No query results for model [...]" }`

---

## Frontend Integration Guide

### Setup

1. **HTTP Client:** Use axios (JS), dio (Dart), or http (Dart)
2. **Store token** in AsyncStorage, flutter_secure_storage, or localStorage
3. **Set base URL:**
```javascript
// JavaScript
const API_BASE = 'http://localhost:8000/api';
// Android emulator: http://10.0.2.2:8000/api
// Physical device: use machine's local IP (e.g. http://192.168.1.x:8000/api)
```

4. **Attach token to requests:**
```javascript
// Axios
axios.defaults.baseURL = 'http://localhost:8000/api';
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
```

```dart
// Dart/Flutter
final dio = Dio(BaseOptions(
    baseUrl: 'http://localhost:8000/api',
    headers: {'Authorization': 'Bearer $token'},
));
```

### Integration Flow

```
App Launch
  Check if token exists in storage
    YES -> GET /api/auth/user -> Show Home
    NO  -> Show Login/Register
           POST /api/auth/login -> Save token -> Show Home

Home Screen
  GET /api/home -> Display trending, top artists, new releases

Song Player
  POST /api/songs/{id}/play -> Get file_url -> Play audio
  POST /api/songs/{id}/like -> Toggle heart icon

Search
  GET /api/search?q=neon -> Display results

Library
  GET /api/me/liked-songs -> Liked songs tab
  GET /api/me/playlists -> Playlists tab

Playlist Management
  POST /api/playlists -> Create playlist
  POST /api/playlists/{id}/songs -> Add song
  DELETE /api/playlists/{id}/songs/{song_id} -> Remove song
  PUT /api/playlists/{id}/reorder -> Reorder songs
```

### Audio Player

```javascript
// React Native with TrackPlayer
import TrackPlayer from 'react-native-track-player';

const response = await api.post(`/songs/${songId}/play`);
await TrackPlayer.add({
    id: song.id,
    url: response.data.file_url,
    title: song.title,
    artist: song.artist.stage_name,
    artwork: song.cover_image,
});
await TrackPlayer.play();
```

```dart
// Flutter with just_audio
final response = await dio.post('/songs/$songId/play');
final audioUrl = response.data['file_url'];
final player = AudioPlayer();
await player.setUrl(audioUrl);
await player.play();
```

### Pagination

```javascript
const fetchPage = async (url) => {
    const res = await axios.get(url);
    return {
        items: res.data.data,
        nextPage: res.data.links.next,
        prevPage: res.data.links.prev,
        totalPages: res.data.meta.last_page,
        total: res.data.meta.total,
    };
};
```

### CORS

If frontend is on a different port/domain, publish and edit CORS config:

```bash
php artisan config:publish cors
```

In `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:3000'],
```

---

## Limitations and Known Issues

### HIGH Severity

**No file upload endpoint**
Songs and images have `file_path`/`cover_image` fields but no upload API. Files must be placed manually in `storage/app/public/` and linked via `php artisan storage:link`.

**No artist/album/song CRUD**
No endpoints to create, update, or delete music content. The API is read-only. Data comes from seeder only.

**`for-you` bug in DiscoverController**
```php
// BUG: pluck('songs.id') gets SONG IDs, not GENRE IDs
$likedGenreIds = $user->likedSongs()->pluck('songs.id');
// Then compares against genre_id column
->whereIn('genre_id', $likedGenreIds)
```

**Fix:**
```php
$likedSongIds = $user->likedSongs()->pluck('songs.id');
$likedGenreIds = DB::table('song_genres')
    ->whereIn('song_id', $likedSongIds)
    ->pluck('genre_id');
```

### MEDIUM Severity

**Basic search** - Uses SQL LIKE which is slow, no fuzzy matching or relevance. No full-text search on SQLite.

**No audio streaming** - The play endpoint returns a full URL. No Range header support for partial content streaming.

**No rate limiting** - No rate limiting middleware configured. Security risk in production.

**No password reset** - Table exists but no endpoints implemented.

**No real-time features** - No WebSockets or broadcasting for live notifications.

### LOW Severity

**Recently played duplicates** - Rapidly playing same song creates multiple entries. Should use `updateOrCreate`.

**No premium enforcement** - `is_premium` field exists but premium content is not gated.

**Playlist follower count** - Never incremented or decremented on any action.

---

## Future Improvements

### Priority 1 - Must Have
- [ ] File upload endpoints (songs, cover art) with validation
- [ ] CRUD for artists, albums, songs (admin/artist roles)
- [ ] Fix the `for-you` genre subquery bug
- [ ] Audio streaming with Range headers
- [ ] Rate limiting middleware
- [ ] Password reset flow
- [ ] Comprehensive input validation

### Priority 2 - Should Have
- [ ] MySQL/PostgreSQL for full-text search
- [ ] Elasticsearch or Meilisearch for advanced search
- [ ] Queue jobs for audio processing (normalize, waveform)
- [ ] Premium content gating via subscriptions
- [ ] Report/flag content endpoints
- [ ] User-to-user following
- [ ] Playlist collaboration/sharing

### Priority 3 - Nice to Have
- [ ] WebSocket broadcasting for live sessions
- [ ] Recommendation engine
- [ ] Play count analytics and charts
- [ ] Artist dashboard with stats
- [ ] Social features (comments, sharing)
- [ ] Offline download with DRM

---

## Project Structure

```
music-platform-backend/
  app/
    Http/
      Controllers/Api/
        AuthController.php
        SongController.php
        AlbumController.php
        ArtistController.php
        PlaylistController.php
        GenreController.php
        SearchController.php
        HomeController.php
        DiscoverController.php
      Resources/
        UserResource.php
        ArtistResource.php
        SongResource.php
        AlbumResource.php
        PlaylistResource.php
        GenreResource.php
    Models/
      User.php
      Artist.php
      Song.php
      Album.php
      Genre.php
      Playlist.php
      RecentlyPlayed.php
      UserSubscription.php
  database/
    migrations/   (12 migration files)
    seeders/DatabaseSeeder.php
  routes/api.php
  config/
  .env
  composer.json
```

---

## Quick Reference Card

```
AUTH:
  POST   /api/auth/register           {name, username, email, password, password_confirmation}
  POST   /api/auth/login              {email, password}
  POST   /api/auth/logout             [AUTH]
  GET    /api/auth/user               [AUTH]
  PUT    /api/auth/profile            [AUTH] {name?, username?, avatar?, country?}
  PUT    /api/auth/password           [AUTH] {current_password, password, password_confirmation}

CONTENT:
  GET    /api/home
  GET    /api/search                  ?q=&type=songs|artists|albums|playlists
  GET    /api/songs                   ?artist_id=&album_id=&genre_id=&per_page=
  GET    /api/songs/{id}
  POST   /api/songs/{id}/play         [AUTH]
  POST   /api/songs/{id}/like         [AUTH]
  POST   /api/songs/{id}/increment-play
  GET    /api/me/liked-songs          [AUTH]

ALBUMS:
  GET    /api/albums                  ?artist_id=&type=
  GET    /api/albums/{id}
  GET    /api/albums/{id}/songs

ARTISTS:
  GET    /api/artists
  GET    /api/artists/{id}
  GET    /api/artists/{id}/songs
  GET    /api/artists/{id}/albums
  POST   /api/artists/{id}/follow     [AUTH]
  GET    /api/artists/{id}/followers

GENRES:
  GET    /api/genres
  GET    /api/genres/{slug}

DISCOVER:
  GET    /api/discover/for-you        [AUTH]
  GET    /api/discover/top-charts
  GET    /api/discover/new-releases

PLAYLISTS:
  GET    /api/playlists
  GET    /api/playlists/{id}
  POST   /api/playlists               [AUTH] {name, description?, is_public?}
  PUT    /api/playlists/{id}          [AUTH] {name?, description?, is_public?}
  DELETE /api/playlists/{id}          [AUTH]
  POST   /api/playlists/{id}/songs    [AUTH] {song_id}
  DELETE /api/playlists/{id}/songs/{song_id}  [AUTH]
  PUT    /api/playlists/{id}/reorder  [AUTH] {song_order: [id, id, ...]}
  GET    /api/me/playlists            [AUTH]
```
