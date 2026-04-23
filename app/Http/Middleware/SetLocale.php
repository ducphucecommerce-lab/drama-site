<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SetLocale
{
    // Map quốc gia -> ngôn ngữ
    private array $countryLangMap = [
        'VN' => 'vi', // Việt Nam
        'US' => 'en', // Mỹ
        'GB' => 'en', // Anh
        'AU' => 'en', // Úc
        'CA' => 'en', // Canada
        'ID' => 'id', // Indonesia
        'TH' => 'th', // Thái Lan
        'SA' => 'ar', // Ả Rập
        'AE' => 'ar', // UAE
        'EG' => 'ar', // Ai Cập
        'MY' => 'en', // Malaysia
        'SG' => 'en', // Singapore
        'PH' => 'en', // Philippines
        'KR' => 'en', // Hàn Quốc
        'JP' => 'en', // Nhật Bản
        'CN' => 'en', // Trung Quốc
    ];

    // Ngôn ngữ API hỗ trợ
    private array $supportedLangs = ['en', 'vi'];

    public function handle(Request $request, Closure $next)
    {
        // 1. Ưu tiên user tự chọn (query param)
        if ($request->has('lang')) {
            $lang = $request->input('lang');
            if (in_array($lang, $this->supportedLangs)) {
                session(['lang' => $lang, 'lang_manual' => true]);
            }
        }

        // 2. Nếu user chưa tự chọn → detect theo IP
        if (!session('lang_manual') || !session('lang')) {
            $lang = $this->detectLangByIp($request->ip());
            session(['lang' => $lang]);
        }

        $lang = session('lang', 'en');

        // Đảm bảo lang hợp lệ
        if (!in_array($lang, $this->supportedLangs)) {
            $lang = 'en';
            session(['lang' => 'en']);
        }

        app()->setLocale($lang);

        return $next($request);
    }

    private function detectLangByIp(string $ip): string
    {
        // Localhost → mặc định tiếng Việt
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return 'vi';
        }

        // Cache 24h để không gọi API nhiều lần
        return Cache::remember('geo:' . $ip, now()->addHours(24), function () use ($ip) {
            try {
                // Dùng ip-api.com (miễn phí, 45 req/phút)
                $res = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=countryCode");

                if ($res->successful()) {
                    $country = $res->json('countryCode', 'US');
                    return $this->countryLangMap[$country] ?? 'en';
                }
            } catch (\Throwable $e) {
                // Fallback nếu API lỗi
            }

            return 'en';
        });
    }
}
