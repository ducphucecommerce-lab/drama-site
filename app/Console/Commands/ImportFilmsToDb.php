<?php
namespace App\Console\Commands;

use App\Models\Film;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ImportFilmsToDb extends Command
{
    protected $signature = 'films:import {platform=all}';
    protected $description = 'Import films from cache to database';

    public function handle()
    {
        if ($this->argument('platform') === 'all') {
            // Lấy tất cả platforms có trong cache
            $allKeys = \Illuminate\Support\Facades\Cache::get('fetched_platforms', 
                ['shortmax','reelshort','dramanova','freereels','netshort','goodshort','dramawave','flickreels','melolo','meloshort','flextv','dramarush','dramanova','starshort','dramapops','snackshort','reelife','dramabite','bilitv','idrama']
            );
            $platforms = $allKeys;
        } else {
            $platforms = [$this->argument('platform')];
        }

        foreach ($platforms as $platform) {
            foreach (['vi', 'en'] as $lang) {
                $films = Cache::get("drama:all:{$platform}:{$lang}", []);
                if (empty($films)) {
                    $this->warn("No cache for {$platform}:{$lang}");
                    continue;
                }

                $count = 0;
                foreach ($films as $film) {
                    Film::updateOrCreate(
                        [
                            'drama_id' => $film['id'],
                            'platform' => $platform,
                            'lang'     => $lang,
                        ],
                        [
                            'title'    => $film['title'] ?? '',
                            'author'   => $film['author'] ?? null,
                            'cover'    => $film['cover'] ?? null,
                            'synopsis' => $film['synopsis'] ?? null,
                            'status'   => $film['status'] ?? null,
                            'views'    => $film['views'] ?? null,
                            'chapters' => $film['chapters'] ?? 0,
                            'genres'   => $film['genres'] ?? [],
                            'tags'     => $film['tags'] ?? [],
                            'raw_data' => $film,
                        ]
                    );
                    $count++;
                }
                $this->info("Imported {$count} films for {$platform}:{$lang}");
            }
        }
    }
}
