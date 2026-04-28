<?php
namespace App\Console\Commands;

use App\Models\Film;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchFilmDetails extends Command
{
    protected $signature = 'films:fetch-details {platform=all} {--limit=50}';
    protected $description = 'Fetch detail data for all films and save to DB';

    public function handle()
    {
        $secret = config('services.api_drama.secret');
        $base   = config('services.api_drama.base');

        $query = Film::whereNull('detail_data')
            ->orWhere('detail_fetched', false);

        if ($this->argument('platform') !== 'all') {
            $query->where('platform', $this->argument('platform'));
        }

        // Chỉ lấy 1 lang (vi) để tiết kiệm quota
        $query->where('lang', 'vi');

        $films = $query->limit((int)$this->option('limit'))->get();

        $this->info("Fetching details for {$films->count()} films...");

        $success = 0;
        $failed  = 0;

        foreach ($films as $film) {
            $path = "/api/v2/detail?category_p={$film->platform}&id={$film->drama_id}&lang=vi";
            $timestamp = (string)(int)(microtime(true) * 1000);
            $signature = hash_hmac('sha256', "GET:{$path}:{$timestamp}", $secret);

            try {
                $res = Http::timeout(10)->withHeaders([
                    'X-Timestamp' => $timestamp,
                    'X-Signature' => $signature,
                    'Accept'      => 'application/json',
                ])->get($base . $path);

                if ($res->status() === 429) {
                    $this->warn("Quota exceeded! Stopping.");
                    break;
                }

                if ($res->successful() && $res->json('data')) {
                    $detail = $res->json('data');

                    // Cập nhật cả vi lẫn en trong DB
                    Film::where('drama_id', $film->drama_id)
                        ->where('platform', $film->platform)
                        ->update([
                            'detail_data'    => json_encode($detail),
                            'detail_fetched' => true,
                        ]);

                    $success++;
                    $this->line("✓ {$film->drama_id} - {$film->title}");
                } else {
                    $failed++;
                    $this->warn("✗ {$film->drama_id} status: {$res->status()}");
                }

                // Tránh spam API
                usleep(500000); // 0.5s delay

            } catch (\Throwable $e) {
                $failed++;
                $this->error("Error: {$e->getMessage()}");
            }
        }

        $this->info("Done! Success: {$success}, Failed: {$failed}");
    }
}
