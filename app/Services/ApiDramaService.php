<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiDramaService
{
    private string $base;
    private string $secret;

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
                $res = Http::timeout(10)->withHeaders($this->buildHeaders($path))->get($this->base . $path);
                if ($res->successful()) return $res->json();
                Log::warning('API Drama error', ['status' => $res->status(), 'path' => $path]);
                return null;
            } catch (\Throwable $e) {
                Log::error('API Drama: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function getAllFilms(string $platform = 'shortmax'): array
    {
        $lang = $this->getLang();

        // Query từ DB — nhanh hơn cache file
        $films = \App\Models\Film::where('platform', $platform)
            ->where('lang', $lang)
            ->get(['raw_data'])
            ->pluck('raw_data')
            ->toArray();

        // Fallback sang vi nếu en không có
        if (empty($films) && $lang !== 'vi') {
            $films = \App\Models\Film::where('platform', $platform)
                ->where('lang', 'vi')
                ->get(['raw_data'])
                ->pluck('raw_data')
                ->toArray();
        }

        return $films;
    }

    private function fetchFromApi(string $platform, string $lang): array
    {
        $allFilms = [];
        for ($page = 1; $page <= 15; $page++) {
            $path = "/api/v2/discover?category_p={$platform}&lang={$lang}&page={$page}";
            try {
                $res   = Http::timeout(10)->withHeaders($this->buildHeaders($path))->get($this->base . $path);
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

        // Goi API realtime - luon co data moi nhat
        $data = $this->get('detail', [
            'category_p' => $platform,
            'id'         => $id,
            'lang'       => $lang,
        ], 60);

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
        $workerBase = 'https://still-night-6adddrama-proxy.elsuvd.workers.dev/hls-proxy/';
        foreach (['1080p', '720p', '480p', 'auto'] as $q) {
            foreach ($streams as $s) {
                if (($s['quality'] ?? '') === $q) {
                    return $workerBase . urlencode($s['url']);
                }
            }
        }
        $url = $streams[0]['url'] ?? null;
        return $url ? $workerBase . urlencode($url) : null;
    }

    // Xoa dau tieng Viet de tim kiem
    private function removeAccents(string $str): string
    {
        $accents = [
            'a' => ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a'],
            'e' => ['e','e','e','e','e','e','e','e','e','e'],
            'i' => ['i','i','i','i'],
            'o' => ['o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o'],
            'u' => ['u','u','u','u','u','u','u','u','u','u'],
            'y' => ['y','y','y','y','y'],
            'd' => ['d'],
        ];

        // Dung iconv neu co
        $result = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        if ($result !== false) return mb_strtolower($result);

        // Fallback manual
        $str = mb_strtolower($str);
        $map = [
            'a' => ["\xc3\xa0","\xc3\xa1","\xc3\xa2","\xc3\xa3","\xe1\xba\xa3","\xe1\xba\xa1","\xe1\xba\xaf","\xe1\xba\xb7","\xe1\xba\xb3","\xe1\xba\xb5","\xe1\xba\xa5","\xe1\xba\xa7","\xe1\xba\xa9","\xe1\xba\xab","\xe1\xba\xad","\xc4\x83","\xc3\xa2"],
            'e' => ["\xc3\xa8","\xc3\xa9","\xc3\xaa","\xe1\xba\xbb","\xe1\xba\xb9","\xe1\xba\xbf","\xe1\xbb\x81","\xe1\xbb\x83","\xe1\xbb\x85","\xe1\xbb\x87"],
            'i' => ["\xc3\xac","\xc3\xad","\xe1\xbb\x89","\xe1\xbb\x8b"],
            'o' => ["\xc3\xb2","\xc3\xb3","\xc3\xb4","\xc3\xb5","\xe1\xbb\x8f","\xe1\xbb\x8d","\xe1\xbb\x91","\xe1\xbb\x93","\xe1\xbb\x95","\xe1\xbb\x97","\xe1\xbb\x99","\xc6\xa1","\xe1\xbb\x9b","\xe1\xbb\x9d","\xe1\xbb\x9f","\xe1\xbb\xa1","\xe1\xbb\xa3"],
            'u' => ["\xc3\xb9","\xc3\xba","\xe1\xbb\xa7","\xe1\xbb\xa5","\xe1\xbb\xa9","\xe1\xbb\xab","\xe1\xbb\xad","\xe1\xbb\xaf","\xe1\xbb\xb1","\xc6\xb0"],
            'y' => ["\xc3\xbd","\xe1\xbb\xb3","\xe1\xbb\xb7","\xe1\xbb\xb9","\xe1\xbb\xb5"],
            'd' => ["\xc4\x91"],
        ];
        foreach ($map as $replacement => $chars) {
            $str = str_replace($chars, $replacement, $str);
        }
        return $str;
    }

    public function search(string $keyword, string $platform = 'shortmax', int $page = 1): array
    {
        $allFilms    = $this->getAllFilms($platform);
        $keyword     = mb_strtolower(trim($keyword));
        $keywordNorm = $this->removeAccents($keyword);

        $results = array_filter($allFilms, function ($f) use ($keyword, $keywordNorm) {
            $title    = $this->removeAccents(mb_strtolower($f['title'] ?? ''));
            $synopsis = $this->removeAccents(mb_strtolower($f['synopsis'] ?? ''));
            return str_contains($title, $keywordNorm) ||
                   str_contains($synopsis, $keywordNorm) ||
                   str_contains(mb_strtolower($f['title'] ?? ''), $keyword);
        });

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
        return [
            'shortmax'   => 'ShortMax',
            'reelshort'  => 'ReelShort',
            'dramanova'  => 'DramaNova',
            'freereels'  => 'FreeReels',
            'netshort'   => 'NetShort',
            'goodshort'  => 'GoodShort',
            'dramawave'  => 'DramaWave',
            'flickreels' => 'FlickReels',
            'melolo'     => 'Melolo',
            'meloshort'  => 'MeloShort',
            'flextv'     => 'FlexTV',
            'dramarush'  => 'DramaRush',
            'rapidtv'    => 'RapidTV',
            'stardusttv' => 'StardustTV',
            'fundrama'   => 'FunDrama',
            'starshort'  => 'StarShort',
            'dramapops'  => 'Dramapops',
            'snackshort' => 'SnackShort',
            'reelife'    => 'Reelife',
            'dramabite'  => 'DramaBite',
            'sodareels'  => 'SodaReels',
            'bilitv'     => 'BiliTV',
            'idrama'     => 'iDrama',
        ];
    }

    public function getBanner(string $platform = 'shortmax'): array
    {
        $lang = $this->getLang();
        $data = $this->get('banner', ['category_p' => $platform, 'lang' => $lang], 720);
        return $data['data'] ?? [];
    }

    public function setLang(string $lang): self { return $this; }
}