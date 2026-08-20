<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() > 0) {
            return;
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@musicplatform.com',
            'password' => 'password',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create regular users
        $users = collect();
        for ($i = 1; $i <= 5; $i++) {
            $users->push(User::create([
                'name' => "User $i",
                'username' => "user{$i}",
                'email' => "user{$i}@example.com",
                'password' => 'password',
                'role' => 'user',
                'is_premium' => $i <= 2,
                'email_verified_at' => now(),
            ]));
        }

        // Create genres
        $genreNames = [
            'Pop', 'Rock', 'Hip-Hop', 'R&B', 'Electronic',
            'Jazz', 'Classical', 'Country', 'Metal', 'Indie',
            'Latin', 'Reggae', 'Folk', 'Blues', 'Punk',
        ];

        $genres = collect();
        foreach ($genreNames as $name) {
            $genres->push(Genre::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]));
        }

        // Create artists
        $artistData = [
            ['name' => 'The Neon Waves', 'stage_name' => 'The Neon Waves', 'bio' => 'Electronic synth-pop duo from Berlin.', 'monthly_listeners' => 5000000, 'verified' => true],
            ['name' => 'Luna Stark', 'stage_name' => 'Luna Stark', 'bio' => 'Indie pop singer-songwriter.', 'monthly_listeners' => 3200000, 'verified' => true],
            ['name' => 'DJ Volt', 'stage_name' => 'DJ Volt', 'bio' => 'House and techno producer from Amsterdam.', 'monthly_listeners' => 8100000, 'verified' => true],
            ['name' => 'Ruby Chen', 'stage_name' => 'Ruby Chen', 'bio' => 'R&B vocalist with jazz influences.', 'monthly_listeners' => 1500000, 'verified' => false],
            ['name' => 'Ironclad', 'stage_name' => 'Ironclad', 'bio' => 'Heavy metal band from Detroit.', 'monthly_listeners' => 900000, 'verified' => false],
            ['name' => 'Milo Reyes', 'stage_name' => 'Milo Reyes', 'bio' => 'Latin pop artist.', 'monthly_listeners' => 2800000, 'verified' => true],
            ['name' => 'Skyline', 'stage_name' => 'Skyline', 'bio' => 'Alternative rock quartet.', 'monthly_listeners' => 4100000, 'verified' => true],
            ['name' => 'Velvet', 'stage_name' => 'Velvet', 'bio' => 'Neo-soul project.', 'monthly_listeners' => 700000, 'verified' => false],
        ];

        $artists = collect();
        foreach ($artistData as $index => $data) {
            $artistUser = User::create([
                'name' => $data['name'],
                'username' => Str::slug($data['stage_name']),
                'email' => Str::slug($data['stage_name']).'@artist.com',
                'password' => 'password',
                'role' => 'artist',
                'email_verified_at' => now(),
            ]);

            $artists->push(Artist::create([
                'user_id' => $artistUser->id,
                'stage_name' => $data['stage_name'],
                'bio' => $data['bio'],
                'monthly_listeners' => $data['monthly_listeners'],
                'verified' => $data['verified'],
            ]));
        }

        // Create albums
        $albumData = [
            ['title' => 'Neon Dreams', 'type' => 'album', 'label' => 'Wave Records', 'release_date' => '2025-11-15'],
            ['title' => 'Midnight Pulse', 'type' => 'album', 'label' => 'Wave Records', 'release_date' => '2026-03-20'],
            ['title' => 'Stardust', 'type' => 'album', 'label' => 'Indie Collective', 'release_date' => '2026-01-10'],
            ['title' => 'Electric Nights', 'type' => 'single', 'label' => 'Volt Music', 'release_date' => '2026-06-01'],
            ['title' => 'Urban Garden', 'type' => 'album', 'label' => 'Soul Kitchen', 'release_date' => '2025-09-22'],
            ['title' => 'Forge', 'type' => 'album', 'label' => 'Heavy Metal Inc', 'release_date' => '2026-02-14'],
            ['title' => 'Fuego', 'type' => 'album', 'label' => 'Latin Vibes', 'release_date' => '2026-05-05'],
            ['title' => 'Echoes', 'type' => 'ep', 'label' => 'Skyline Music', 'release_date' => '2026-04-18'],
            ['title' => 'After Hours', 'type' => 'album', 'label' => 'Velvet Sounds', 'release_date' => '2025-12-01'],
            ['title' => 'Summer Singles', 'type' => 'compilation', 'label' => 'Various', 'release_date' => '2026-07-01'],
        ];

        $albums = collect();
        foreach ($albumData as $index => $data) {
            $artistIndex = $index % $artists->count();
            $albums->push(Album::create([
                'artist_id' => $artists[$artistIndex]->id,
                'title' => $data['title'],
                'release_date' => $data['release_date'],
                'type' => $data['type'],
                'label' => $data['label'],
            ]));
        }

        // Create songs
        $songTitles = [
            'Electric Sunrise', 'Neon Lights', 'Pulse', 'Dreaming', 'Midnight Drive',
            'Falling Stars', 'City Lights', 'Ocean Waves', 'Firefly', 'Shadow Dance',
            'Crystal Clear', 'Violet Hour', 'Golden Hour', 'Moonrise', 'Thunder',
            'Silk Road', 'Cosmic Love', 'Deep Blue', 'Wildfire', 'Sunset Boulevard',
            'Gravity', 'Starlight', 'After Dark', 'Sweet Dreams', 'Voltage',
            'Iron Heart', 'Steel Wings', 'Flame On', 'Rising Sun', 'Storm Chaser',
            'Baila Conmigo', 'Fuego Lento', 'Noche de Estrellas', 'Ritmo Caliente', 'Sol y Luna',
            'Highway One', 'Horizon', 'Echo Chamber', 'Waves Breaking', 'Fade to Grey',
            'Velvet Touch', 'Smooth Operator', 'Late Night Jazz', 'Coffee & Cream', 'Blue Note',
        ];

        $songs = collect();
        $trackNumber = 1;
        $currentAlbumIndex = 0;

        foreach ($songTitles as $index => $title) {
            if ($index > 0 && $index % 5 === 0) {
                $trackNumber = 1;
                $currentAlbumIndex = min($currentAlbumIndex + 1, $albums->count() - 1);
            }

            $song = Song::create([
                'artist_id' => $albums[$currentAlbumIndex]->artist_id,
                'album_id' => $albums[$currentAlbumIndex]->id,
                'title' => $title,
                'file_path' => "songs/{$title}.mp3",
                'duration' => rand(150, 360),
                'track_number' => $trackNumber,
                'play_count' => rand(1000, 500000),
                'is_explicit' => rand(0, 10) > 8,
            ]);

            // Assign 1-3 random genres
            $genreCount = rand(1, 3);
            $song->genres()->attach($genres->random($genreCount)->pluck('id'));

            $songs->push($song);
            $trackNumber++;
        }

        // Create playlists
        $playlistData = [
            ['name' => 'Today\'s Top Hits', 'description' => 'The most played songs right now.', 'user_id' => $admin->id],
            ['name' => 'Chill Vibes', 'description' => 'Relax and unwind with these mellow tracks.', 'user_id' => $users[0]->id],
            ['name' => 'Workout Mix', 'description' => 'High energy songs to fuel your workout.', 'user_id' => $users[1]->id],
            ['name' => 'Late Night Feels', 'description' => 'Smooth R&B and soul for the evening.', 'user_id' => $users[2]->id],
            ['name' => 'Road Trip', 'description' => 'Songs for the open road.', 'user_id' => $users[3]->id],
            ['name' => 'Focus Mode', 'description' => 'Instrumental and ambient tracks for concentration.', 'user_id' => $users[4]->id],
        ];

        foreach ($playlistData as $data) {
            $playlist = Playlist::create($data);
            $playlist->songs()->attach($songs->random(rand(5, 15))->pluck('id')->toArray());
        }

        // Create follows
        foreach ($users as $user) {
            $user->followedArtists()->attach($artists->random(rand(2, 4))->pluck('id')->toArray());
        }

        // Create likes
        foreach ($users as $user) {
            $user->likedSongs()->attach($songs->random(rand(3, 10))->pluck('id')->toArray());
        }

        // Create subscriptions
        foreach ($users as $index => $user) {
            UserSubscription::create([
                'user_id' => $user->id,
                'plan' => $user->is_premium ? 'premium' : 'free',
                'starts_at' => now()->subMonths(3),
                'expires_at' => $user->is_premium ? now()->addMonths(9) : null,
                'is_active' => true,
            ]);
        }
    }
}
