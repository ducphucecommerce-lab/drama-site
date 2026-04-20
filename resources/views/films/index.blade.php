@extends('layouts.app')
@section('title', 'Trang chủ - Xem phim ngắn miễn phí')

@section('content')
<div class="container">

  {{-- Hero VIP Banner (nếu chưa VIP) --}}
  @auth
    @if(!auth()->user()->isVip())
      <div class="vip-banner">
        <div>
          <h3>🌟 Nâng cấp VIP để xem không giới hạn</h3>
          <p>Mở khóa toàn bộ 22+ nền tảng phim ngắn — chỉ từ 99.000đ/tháng</p>
        </div>
        <a href="{{ route('subscription.index') }}" class="btn-vip-large">Mua VIP ngay</a>
      </div>
    @endif
  @else
    <div class="vip-banner">
      <div>
        <h3>🎬 Đăng ký miễn phí để lưu lịch sử xem</h3>
        <p>Hoặc mua VIP để xem không giới hạn từ 22+ nền tảng</p>
      </div>
      <a href="{{ route('register') }}" class="btn-outline-light">Đăng ký miễn phí</a>
    </div>
  @endauth

  {{-- Tab --}}
  <div class="tabs">
    <a href="{{ route('home', ['platform' => request('platform', 'dramabox')]) }}"
       class="tab {{ request('tab', 'recommend') === 'recommend' ? 'active' : '' }}">🔥 Đề xuất</a>
    <a href="{{ route('home', ['tab' => 'trending', 'platform' => request('platform', 'dramabox')]) }}"
       class="tab {{ request('tab') === 'trending' ? 'active' : '' }}">📈 Xu hướng</a>
  </div>

  {{-- Film Grid --}}
  @if(empty($films))
    <div class="empty-state">
      <p>😕 Không có phim nào. Vui lòng kiểm tra API key hoặc thử lại sau.</p>
    </div>
  @else
    <div class="film-grid">
      @foreach($films as $film)
        <a href="{{ route('films.detail', $film['id'] ?? $film['drama_id'] ?? '#') }}?platform={{ $platform }}"
           class="film-card">
          <div class="card-thumb">
            <img src="{{ $film['cover'] ?? $film['cover_url'] ?? asset('img/no-cover.png') }}"
                 alt="{{ $film['title'] ?? 'Phim ngắn' }}" loading="lazy">
            <div class="card-overlay">
              <span class="play-btn">▶</span>
              @if(isset($film['episodes']))
                <span class="ep-count">{{ $film['episodes'] }} tập</span>
              @endif
            </div>
            {{-- Free badge cho 3 tập đầu --}}
            <span class="free-badge">3 tập miễn phí</span>
          </div>
          <div class="card-info">
            <p class="card-title">{{ Str::limit($film['title'] ?? 'Phim ngắn', 50) }}</p>
            @if(isset($film['genre']))
              <p class="card-genre">{{ $film['genre'] }}</p>
            @endif
          </div>
        </a>
      @endforeach
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrap">
      @if($page > 1)
        <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" class="btn-page">← Trước</a>
      @endif
      <span>Trang {{ $page }}</span>
      <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" class="btn-page">Sau →</a>
    </div>
  @endif

</div>
@endsection
