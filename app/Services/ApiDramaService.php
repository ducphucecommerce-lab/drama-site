<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiDramaService
{
    private string $base;
    private string $secret;

    // Ngôn ng? l?y t? session user
    private function getLang(): string
    {
        return session('lang', 'en');
    }

    public function __construct()
    {
        $this->base   = rtrim(config('services.api_drama.base', 'https://api-drama.dobda.id'), '/');
        $this->secret = config('services.api_drama.secret', '');
    }

    private function buildHeaders(string $path): array
    {
        $timestamp = (string)(int)(microtime(true) * 1000);
        $signature = hash_hmac('sha256', "GET:{$path}:{$timestamp}", $this->secret);
        return [
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
            'Accept'      => 'application/json',
        ];
    }

    private function get(string $endpoint, array $params = [], int $cacheMins = 60): ?array
    {
        $query    = http_build_query(array_filter($params));
        $path     = '/api/v2/' . ltrim($endpoint, '/') . ($query ? '?' . $query : '');
        $cacheKey = 'drama:' . md5($path);

        return Cache::remember($cacheKey, now()->addMinutes($cacheMins), function () use ($path) {
            try {
                $res = Http::timeout(30)->withHeaders($this->buildHeaders($path))->get($this->base . $path);
                if ($res->successful()) return $res->json();
                Log::warning('API Drama error', ['status' => $res->status(), 'path' => $path]);
                return null;
            } catch (\Throwable $e) {
                Log::error('API Drama: ' . $e->getMessage());
                return null;
            }
        });
    }

    // L?y t?t c? phim t? cache (dã fetch b?ng artisan command)
    public function getAllFilms(string $platform = 'shortmax'): array
    {
        $lang     = $this->getLang();
        $cacheKey = "drama:all:{$platform}:{$lang}";

        // Th? l?y t? cache ngôn ng? hi?n t?i
        $films = Cache::get($cacheKey, []);

        // Fallback v? vi n?u không có
        if (empty($films) && $lang !== 'vi') {
            $films = Cache::get("drama:all:{$platform}:vi", []);
        }

        // Fallback fetch realtime n?u cache tr?ng
        if (empty($films)) {
            $films = $this->fetchFromApi($platform, $lang);
            if (!empty($films)) {
                Cache::put($cacheKey, $films, now()->addHours(13));
            }
        }

        return $films;
    }

    private function fetchFromApi(string $platform, string $lang): array
    {
        $allFilms = [];
        for ($page = 1; $page <= 15; $page++) {
            $path = "/api/v2/discover?category_p={$platform}&lang={$lang}&page={$page}";
            try {
                $res   = Http::timeout(30)->withHeaders($this->buildHeaders($path))->get($this->base . $path);
                $data  = $res->json();
                $films = $data['data'] ?? [];
                if (empty($films)) break;
                foreach ($films as $film) {
                    if (!empty($film['id'])) $allFilms[] = $film;
                }
                $total = $data['total'] ?? 0;
                if ($total > 0 && count($allFilms) >= $total) break;
                usleep(300000);
            } catch (\Throwable $e) {
                break;
            }
        }
        return $allFilms;
    }

    public function getHomeList(string $platform = 'shortmax', int $page = 1, int $perPage = 24): array
    {
        $allFilms = $this->getAllFilms($platform);
        $offset   = ($page - 1) * $perPage;
        return array_slice($allFilms, $offset, $perPage);
    }

    public function getTotalFilms(string $platform = 'shortmax'): int
    {
        return count($this->getAllFilms($platform));
    }

    public function getTotalPages(string $platform = 'shortmax', int $perPage = 24): int
    {
        return (int) ceil($this->getTotalFilms($platform) / $perPage);
    }

    public function getDiscover(string $platform = 'shortmax', int $page = 1): array
    {
        return $this->getHomeList($platform, $page);
    }

    public function getTrending(string $platform = 'shortmax'): array
    {
        $films = $this->getAllFilms($platform);
        usort($films, function ($a, $b) {
            $va = (float) preg_replace('/[^0-9.]/', '', str_ireplace(['k','m'], ['000','000000'], $a['views'] ?? '0'));
            $vb = (float) preg_replace('/[^0-9.]/', '', str_ireplace(['k','m'], ['000','000000'], $b['views'] ?? '0'));
            return $vb <=> $va;
        });
        return array_slice($films, 0, 24);
    }

    public function getDetail(string $id, string $platform = 'shortmax'): ?array
    {
        $lang = $this->getLang();
        $data = $this->get('detail', [
            'category_p' => $platform,
            'id'         => $id,
            'lang'       => $lang,
        ], 120);

        if (!$data || !isset($data['data'])) return null;
        $detail = $data['data'];

        if (isset($detail['chapters']) && is_array($detail['chapters'])) {
            $detail['episode_list'] = array_map(fn($ch) => [
                'episode' => $ch['index'] ?? $ch['id'],
                'id'      => $ch['id'],
                'title'   => $ch['title'] ?? 'Episode ' . ($ch['index'] ?? $ch['id']),
            ], $detail['chapters']);
        }
        $detail['episodes'] = $detail['total_episodes'] ?? count($detail['chapters'] ?? []);
        return $detail;
    }

    public function getStreamUrl(string $id, string $platform, int $episode = 1): ?string
    {
        $lang      = $this->getLang();
        $detail    = $this->getDetail($id, $platform);
        $chapterId = $episode;

        if ($detail && isset($detail['chapters'])) {
            foreach ($detail['chapters'] as $ch) {
                if (($ch['index'] ?? null) == $episode) {
                    $chapterId = $ch['id'];
                    break;
                }
            }
        }

        $data = $this->get('video', [
            'category_p' => $platform,
            'id'         => $id,
            'chapterId'  => $chapterId,
            'lang'       => $lang,
        ], 1);

        if (!$data || empty($data['data']['streams'])) return null;
        $streams = $data['data']['streams'];
        foreach (['1080p', '720p', '480p', 'auto'] as $q) {
            foreach ($streams as $s) {
                if (($s['quality'] ?? '') === $q) return $s['url'];
            }
        }
        return $streams[0]['url'] ?? null;
    }

    public function search(string $keyword, string $platform = 'shortmax', int $page = 1): array
    {
        $allFilms = $this->getAllFilms($platform);
        $keyword  = mb_strtolower(trim($keyword));
        $results  = array_filter($allFilms, fn($f) =>
            str_contains(mb_strtolower($f['title'] ?? ''), $keyword) ||
            str_contains(mb_strtolower($f['synopsis'] ?? ''), $keyword)
        );
        return array_values($results);
    }

    public function getByGenre(string $genre, string $platform = 'shortmax', int $page = 1): array
    {
        $allFilms = $this->getAllFilms($platform);
        $genre    = mb_strtolower(trim($genre));
        $results  = array_filter($allFilms, function ($f) use ($genre) {
            $genres = array_map('mb_strtolower', $f['genres'] ?? []);
            return in_array($genre, $genres) || str_contains(implode(' ', $genres), $genre);
        });
        return array_values($results);
    }

    public function getPlatforms(): array
    {
        $data = $this->get('categories', [], 1440);
        if ($data && isset($data['data'])) {
            $result = [];
            foreach ($data['data'] as $cat) {
                $result[$cat['name']] = $cat['display_name'];
            }
            return $result;
        }
        return [
            'shortmax'   => 'ShortMax',
            'freereels'  => 'FreeReels',
            'reelshort'  => 'ReelShort',
            'dramabox'   => 'DramaBox',
            'netshort'   => 'NetShort',
            'goodshort'  => 'GoodShort',
            'dramawave'  => 'DramaWave',
            'flickreels' => 'FlickReels',
        ];
    }

    public function getBanner(string $platform = 'shortmax'): array
    {
        $lang = $this->getLang();
        $data = $this->get('banner', ['category_p' => $platform, 'lang' => $lang], 120);
        return $data['data'] ?? [];
    }

    // Gi? backward compatibility
    public function setLang(string $lang): self { return $this; }
}