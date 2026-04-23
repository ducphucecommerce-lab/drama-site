<?php
namespace App\Http\Controllers;

use App\Models\WatchHistory;
use App\Services\ApiDramaService;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    public function __construct(private ApiDramaService $api) {}

    // Trang chủ
    public function index(Request $request)
    {
        $platform  = $request->get('platform', 'shortmax');
        $tab       = $request->get('tab', 'recommend');
        $page      = (int) $request->get('page', 1);

        $films     = $tab === 'trending'
            ? $this->api->getTrending($platform)
            : $this->api->getHomeList($platform, $page);

        $platforms = $this->api->getPlatforms();

        return view('films.index', compact('films', 'platforms', 'platform', 'tab', 'page'));
    }

    // Chi tiết phim
    public function detail(Request $request, string $id)
    {
        $platform = $request->get('platform', 'shortmax');
        $film     = $this->api->getDetail($id, $platform);

        if (!$film) {
            return redirect()->route('home')->with('error', 'Không tìm thấy phim này.');
        }

        // Ghi lịch sử xem
        if (auth()->check()) {
            WatchHistory::updateOrCreate(
                ['user_id' => auth()->id(), 'drama_id' => $id, 'platform' => $platform],
                ['drama_title' => $film['title'] ?? '', 'cover_url' => $film['cover'] ?? '']
            );
        }

        return view('films.detail', compact('film', 'platform', 'id'));
    }

    // Trang xem phim
    public function watch(Request $request, string $id)
    {
        $platform = $request->get('platform', 'shortmax');
        $episode  = (int) $request->get('ep', 1);

        // Kiểm tra VIP
        $film = $this->api->getDetail($id, $platform);
        if (!$film) abort(404);

        // Tập miễn phí: 3 tập đầu
        $isFreeEp = $episode <= 3;
        $isVip    = auth()->check() && auth()->user()->isVip();

        if (!$isFreeEp && !$isVip) {
            return redirect()->route('subscription.index')
                ->with('warning', 'Bạn cần mua gói VIP để xem tập này.');
        }

        $streamUrl = $this->api->getStreamUrl($id, $platform, $episode);

        return view('films.watch', compact('film', 'platform', 'id', 'episode', 'streamUrl'));
    }

    // Tìm kiếm
    public function search(Request $request)
    {
        $keyword  = trim($request->get('q', ''));
        $platform = $request->get('platform', 'all');
        $results  = [];

        if ($keyword) {
            $results = $this->api->search($keyword, $platform);
        }

        $platforms = $this->api->getPlatforms();

        return view('films.search', compact('keyword', 'results', 'platforms', 'platform'));
    }

    // Lọc theo thể loại
    public function genre(Request $request, string $genre)
    {
        $platform = $request->get('platform', 'all');
        $page     = (int) $request->get('page', 1);
        $films    = $this->api->getByGenre($genre, $platform, $page);
        $platforms= $this->api->getPlatforms();

        return view('films.genre', compact('genre', 'films', 'platforms', 'platform', 'page'));
    }
}
