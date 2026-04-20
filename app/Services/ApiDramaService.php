<?php
namespace App\Services;

class ApiDramaService
{
    // ── Dữ liệu giả để test giao diện ────────────────────────
    private function fakeDramas(int $count = 20): array
    {
        $titles = [
            'Tổng Tài Bí Ẩn', 'Hôn Nhân Sắp Đặt', 'Báo Thù Ngọt Ngào',
            'Em Là Vợ Sếp', 'Thiên Kim Giả Mạo', 'Yêu Lại Từ Đầu',
            'Bí Mật Hào Môn', 'Tình Yêu Định Mệnh', 'Cô Dâu Thay Thế',
            'Triệu Phú Nghèo Khó', 'Nữ Cường Xuyên Không', 'Hoàng Tử Sói',
            'CEO Lạnh Lùng', 'Nàng Tiên Cá', 'Anh Hùng Bị Thất Sủng',
            'Vợ Giả Chồng Thật', 'Tình Địch Trở Thành Tình Nhân',
            'Bác Sĩ Hoàng Gia', 'Đại Tiểu Thư Nổi Loạn', 'Kẻ Phản Diện Dễ Thương',
        ];
        $genres  = ['Tình cảm', 'Hành động', 'Hài hước', 'Cổ trang', 'Hiện đại'];
        $covers  = [
            'https://awscover.netshort.com/tos-vod-mya-v-da59d5a2040f5f77/imageG/production/2041687656382857217/1776493756619-0501666617282046-3比4jpg~tplv-vod-rs:651:868.webp',
            'https://awscover.netshort.com/tos-vod-mya-v-da59d5a2040f5f77/coverG/prod/-1446654500_20260420-152311.jpg~tplv-vod-rs:651:868.webp',
            'https://awscover.netshort.com/tos-vod-mya-v-da59d5a2040f5f77/imageG/production/2041684074564681730/1776587996251-8289870698009258-3比4jpg~tplv-vod-rs:651:868.webp',
            'https://awscover.netshort.com/tos-vod-mya-v-da59d5a2040f5f77/imageG/production/2042218205463707650/1776571577634-.826048795359644-3比4jpg~tplv-vod-rs:651:868.webp',
            'https://awscover.netshort.com/tos-vod-mya-v-da59d5a2040f5f77/imageG/production/2041850940239118338/1776571010951-2107881807857601-3比4jpg~tplv-vod-rs:651:868.webp',
        ];
        $platforms = ['dramabox', 'reelshort', 'netshort', 'goodshort', 'shortmax'];

        $dramas = [];
        for ($i = 0; $i < $count; $i++) {
            $dramas[] = [
                'id'          => 'demo_' . ($i + 1),
                'drama_id'    => 'demo_' . ($i + 1),
                'title'       => $titles[$i % count($titles)],
                'cover'       => $covers[$i % count($covers)],
                'cover_url'   => $covers[$i % count($covers)],
                'episodes'    => rand(20, 80),
                'genre'       => $genres[$i % count($genres)],
                'platform'    => $platforms[$i % count($platforms)],
                'description' => 'Một câu chuyện tình cảm đầy cảm xúc với nhiều twist bất ngờ. Mỗi tập chỉ 1-2 phút nhưng đầy kịch tính và hấp dẫn.',
                'year'        => 2024,
                'rating'      => round(rand(35, 50) / 10, 1),
                'episode_list'=> array_map(fn($e) => ['episode' => $e], range(1, rand(20, 40))),
            ];
        }
        return $dramas;
    }

    // ── Public API methods ────────────────────────────────────
    public function getHomeList(string $platform = 'dramabox', int $page = 1, int $limit = 20): array
    {
        return $this->fakeDramas($limit);
    }

    public function search(string $keyword, string $platform = 'all', int $page = 1): array
    {
        return array_filter(
            $this->fakeDramas(20),
            fn($d) => str_contains(mb_strtolower($d['title']), mb_strtolower($keyword))
        );
    }

    public function getDetail(string $id, string $platform = 'dramabox'): ?array
    {
        $dramas = $this->fakeDramas(20);
        return $dramas[0]; // trả về phim đầu tiên làm demo
    }

    public function getStreamUrl(string $id, string $platform, int $episode = 1): ?string
    {
        // Video mẫu để test player
        return 'https://www.w3schools.com/html/mov_bbb.mp4';
    }

    public function getByGenre(string $genre, string $platform = 'all', int $page = 1): array
    {
        return $this->fakeDramas(20);
    }

    public function getPlatforms(): array
    {
        return [
            'dramabox'  => 'DramaBox',
            'reelshort' => 'ReelShort',
            'netshort'  => 'NetShort',
            'goodshort' => 'GoodShort',
            'shortmax'  => 'ShortMax',
            'dramawave' => 'DramaWave',
            'flextv'    => 'FlexTV',
            'flickreels'=> 'FlickReels',
        ];
    }

    public function getTrending(string $platform = 'all'): array
    {
        return $this->fakeDramas(20);
    }
}