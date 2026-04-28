<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchAllFilms extends Command
{
    protected $signature = 'films:fetch {platform=shortmax} {--pages=20} {--all : Fetch all platforms}';
    protected $description = 'Fetch all films from API and cache them';

    public function handle()
    {
        if ($this->option('all')) {
            $this->fetchAllPlatforms();
            return;
        }

        $this->fetchPlatform($this->argument('platform'), (int) $this->option('pages'));
    }

    private function fetchAllPlatforms()
    {
        // L?y danh sách platforms t? API
        $secret = config('services.api_drama.secret');
        $base   = config('services.api_drama.base');
        $path   = '/api/v2/categories';

        $timestamp = (string)(int)(microtime(true) * 1000);
        $signature = hash_hmac('sha256', "GET:{$path}:{$timestamp}", $secret);

        try {
            $res = Http::timeout(15)->withHeaders([
                'X-Timestamp' => $timestamp,
                'X-Signature' => $signature,
            ])->get($base . $path);

            if ($res->successful()) {
                $data      = $res->json();
                $platforms = array_column($data['data'] ?? [], 'name');
                $this->info("Found " . count($platforms) . " platforms: " . implode(', ', $platforms));
            } else {
                // Fallback danh sách m?c d?nh
                $platforms = ['shortmax', 'freereels', 'reelshort', 'dramabox', 'netshort', 'goodshort', 'dramawave', 'flickreels'];
                $this->warn("Could not fetch platforms, using defaults");
            }
        } catch (\Throwable $e) {
            $platforms = ['shortmax', 'freereels', 'reelshort'];
            $this->error("Error: " . $e->getMessage());
        }

        foreach ($platforms as $platform) {
            $this->info("\n=== Fetching $platform ===");
            $this->fetchPlatform($platform, 20);
            sleep(2); // Delay gi?a các platform
        }

        $this->info("\nAll platforms fetched!");
    }

    private function fetchPlatform(string $platform, int $maxPages): void
    {
        $secret   = config('services.api_drama.secret');
        $base     = config('services.api_drama.base');
        $allFilms = [];

        for ($page = 1; $page <= $maxPages; $page++) {
            $path      = "/api/v2/discover?category_p={$platform}&lang=vi&page={$page}";
            $timestamp = (string)(int)(microtime(true) * 1000);
            $signature = hash_hmac('sha256', "GET:{$path}:{$timestamp}", $secret);

            $success = false;

            for ($try = 1; $try <= 3; $try++) {
                try {
                    $res = Http::timeout(30)->withHeaders([
                        'X-Timestamp' => $timestamp,
                        'X-Signature' => $signature,
                    ])->get($base . $path);

                    if ($res->successful()) {
                        $data  = $res->json();
                        $films = $data['data'] ?? [];

                        if (empty($films)) {
                            $this->info("No more films at page $page, stopping.");
                            break 2;
                        }

                        foreach ($films as $film) {
                            if (!empty($film['id'])) {
                                $allFilms[] = $film;
                            }
                        }

                        $total = $data['total'] ?? '?';
                        $this->info("Page $page: +" . count($films) . " films | Total: " . count($allFilms) . " / $total");

                        if ($total !== '?' && count($allFilms) >= $total) {
                            break 2;
                        }

                        $success = true;
                        usleep(500000);
                        break;
                    }

                    $this->warn("Page $page try $try failed: " . $res->status());
                    sleep(2);

                } catch (\Throwable $e) {
                    $this->error("Error page $page try $try: " . $e->getMessage());
                    sleep(2);
                }
            }

            if (!$success) {
                $this->warn("Skipping page $page");
                continue;
            }
        }

        if (!empty($allFilms)) {
            $cacheKey = "drama:all:{$platform}:vi";
            Cache::put($cacheKey, $allFilms, now()->addHours(13));
            $this->info("Cached " . count($allFilms) . " films for $platform");
        } else {
            $this->warn("No films found for $platform - skipping cache");
        }
    }
}