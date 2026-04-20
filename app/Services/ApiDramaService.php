<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiDramaService
{
    private string $base;
    private string $secret;
    private string $lang = 'vi';

    public function __construct()
    {
        $this->base   = rtrim(config('services.api_drama.base', 'https://api-drama.dobda.id'), '/');
        $this->secret = config('services.api_drama.secret', '');
    }

    private function buildHeaders(string $method, string $path): array
    {
        $timestamp = (string)(int)(microtime(true) * 1000);
        $payload   = strtoupper($method) . ':' . $path . ':' . $timestamp;
        $signature = hash_hmac('sha256', $payload, $this->secret);
        return [
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
            'Accept'      => 'application/json',
        ];
    }

    private function get(string $endpoint, array $params = [], int $cacheMins = 10): ?array
    {
        $query    = http_build_query(array_filter($params));
        $path     = '/api/v2/' . ltrim($endpoint, '/') . ($query ? '?' . $query : '');
        $cacheKey = 'drama:' . md5($path);

        return Cache::remember($cacheKey, now()->addMinutes($cacheMins), function () use ($path) {
            try {
                $headers = $this->buildHeaders('GET', $path);
                $res = Http::timeout(15)->withHeaders($headers)->get($this->base . $path);
                if ($res->successful()) return $res->json();
                Log::warning('API Drama error', ['status' => $res->status(), 'path' => $path]);
                return null;
            } catch (\Throwable $e) {
                Log::error('API Drama: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function getHomeList(string $platform = 'freereels', int $page = 1, int $limit = 20): array
    {
        $data = $this->get('home', ['category_p' => $platform, 'lang' => $this->lang]);
        return $data['data'] ?? [];
    }

    public function getDiscover(string $platform = 'freereels', int $page = 1): array
    {
        $data = $this->get('discover', ['category_p' => $platform, 'lang' => $this->lang, 'page' => $page]);
        return $data['data'] ?? [];
    }

    public function getTrending(string $platform = 'freereels'): array
    {
        return $this->getDiscover($platform, 1);
    }

    public function getDetail(string $id, string $platform = 'freereels'): ?array
    {
        $data = $this->get('detail', ['category_p' => $platform, 'id' => $id, 'lang' => $this->lang], 30);
        if (!$data || !isset($data['data'])) return null;
        $detail = $data['data'];
        if (isset($detail['chapters']) && is_array($detail['chapters'])) {
            $detail['episode_list'] = array_map(fn($ch) => [
                'episode' => $ch['index'] ?? $ch['id'],
                'id'      => $ch['id'],
                'title'   => $ch['title'] ?? 'Tập ' . ($ch['index'] ?? $ch['id']),
            ], $detail['chapters']);
        }
        $detail['episodes'] = $detail['total_episodes'] ?? count($detail['chapters'] ?? []);
        return $detail;
    }

    public function getStreamUrl(string $id, string $platform, int $episode = 1): ?string
    {
        $detail    = $this->getDetail($id, $platform);
        $chapterId = $episode;
        if ($detail && isset($detail['chapters'])) {
            foreach ($detail['chapters'] as $ch) {
                if (($ch['index'] ?? null) == $episode) { $chapterId = $ch['id']; break; }
            }
        }
        $data = $this->get('video', [
            'category_p' => $platform, 'id' => $id,
            'chapterId'  => $chapterId, 'lang' => $this->lang,
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

    public function search(string $keyword, string $platform = 'freereels', int $page = 1): array
    {
        $data = $this->get('search', ['category_p' => $platform, 'q' => $keyword, 'lang' => $this->lang, 'page' => $page], 5);
        return $data['data'] ?? [];
    }

    public function getByGenre(string $genre, string $platform = 'freereels', int $page = 1): array
    {
        return $this->getDiscover($platform, $page);
    }

    public function getPlatforms(): array
    {
        $data = $this->get('categories', [], 1440);
        if ($data && isset($data['data'])) {
            $result = [];
            foreach ($data['data'] as $cat) { $result[$cat['name']] = $cat['display_name']; }
            return $result;
        }
        return [
            'freereels' => 'FreeReels', 'dramabox' => 'DramaBox',
            'reelshort' => 'ReelShort', 'netshort' => 'NetShort',
            'goodshort' => 'GoodShort', 'shortmax' => 'ShortMax',
            'dramawave' => 'DramaWave', 'flickreels' => 'FlickReels',
            'melolo'    => 'Melolo',    'dramabite' => 'DramaBite',
            'reelife'   => 'Reelife',   'rapidtv' => 'RapidTV',
        ];
    }

    public function getBanner(string $platform = 'freereels'): array
    {
        $data = $this->get('banner', ['category_p' => $platform, 'lang' => $this->lang], 30);
        return $data['data'] ?? [];
    }

    public function setLang(string $lang): self { $this->lang = $lang; return $this; }
}
