@extends('layouts.app')
@section('title', 'Xem: ' . ($film['title'] ?? 'Phim') . ' - Tập ' . $episode)

@section('content')
<div class="container watch-page">

  <div class="watch-layout">
    {{-- Main player --}}
    <div class="watch-main">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('films.detail', $id) }}?platform={{ $platform }}">{{ Str::limit($film['title'] ?? '', 30) }}</a>
        <span>/</span>
        <span>Tập {{ $episode }}</span>
      </div>

      {{-- Video player --}}
      <div class="player-wrap">
        @if($streamUrl)
          <video id="player"
            src="{{ $streamUrl }}"
            controls autoplay playsinline
            poster="{{ $film['cover'] ?? '' }}"
            class="video-player">
            Trình duyệt không hỗ trợ video.
          </video>
        @else
          <div class="player-error">
            <div class="player-error-icon">😕</div>
            <p>Không thể tải video này.</p>
            <p style="font-size:12px;color:var(--text3);margin-top:6px">
              Vui lòng thử lại hoặc chọn tập khác.
            </p>
          </div>
        @endif
      </div>

      {{-- Title + episode nav --}}
      <div class="watch-info">
        <h1 class="watch-title">{{ $film['title'] ?? 'Phim ngắn' }} — Tập {{ $episode }}</h1>

        <div class="ep-nav">
          @if($episode > 1)
            <a href="{{ route('films.watch', $id) }}?platform={{ $platform }}&ep={{ $episode - 1 }}"
               class="btn-ep">← Tập trước</a>
          @endif

          @php $totalEps = $film['episodes'] ?? $film['total_episodes'] ?? 0; @endphp
          @if($totalEps && $episode < $totalEps)
            @php $nextEp = $episode + 1; $isFree = $nextEp <= 3; $isVip = auth()->check() && auth()->user()->isVip(); @endphp
            @if($isFree || $isVip)
              <a href="{{ route('films.watch', $id) }}?platform={{ $platform }}&ep={{ $nextEp }}"
                 class="btn-ep btn-ep-next">Tập tiếp → </a>
            @else
              <a href="{{ route('subscription.index') }}" class="btn-ep btn-ep-vip">🔒 VIP để xem tập {{ $nextEp }}</a>
            @endif
          @endif
        </div>

        {{-- Free / VIP notice --}}
        @if($episode <= 3)
          <div class="free-notice">✅ Tập miễn phí — <a href="{{ route('subscription.index') }}">Mua VIP</a> để xem không giới hạn</div>
        @endif
      </div>
    </div>

    {{-- Sidebar: episode list --}}
    <div class="watch-sidebar">
      <h3 class="sidebar-title">Danh sách tập</h3>
      @if(isset($film['episode_list']) && is_array($film['episode_list']))
        <div class="sidebar-eps">
          @foreach($film['episode_list'] as $ep)
            @php
              $epNum    = $ep['episode'] ?? $ep['ep'] ?? $loop->iteration;
              $isFree   = $epNum <= 3;
              $isVip    = auth()->check() && auth()->user()->isVip();
              $canWatch = $isFree || $isVip;
              $active   = $epNum == $episode;
            @endphp
            <a href="{{ $canWatch ? route('films.watch', $id).'?platform='.$platform.'&ep='.$epNum : route('subscription.index') }}"
               class="sidebar-ep {{ $active ? 'active' : '' }} {{ !$canWatch ? 'locked' : '' }}">
              Tập {{ $epNum }}
              @if(!$canWatch) <span>🔒</span> @endif
              @if($active) <span class="now-playing">▶</span> @endif
            </a>
          @endforeach
        </div>
      @else
        {{-- Fallback: show numbered episodes --}}
        @php $total = $film['episodes'] ?? 20; @endphp
        <div class="sidebar-eps">
          @for($i = 1; $i <= $total; $i++)
            @php
              $isFree   = $i <= 3;
              $isVip    = auth()->check() && auth()->user()->isVip();
              $canWatch = $isFree || $isVip;
              $active   = $i == $episode;
            @endphp
            <a href="{{ $canWatch ? route('films.watch', $id).'?platform='.$platform.'&ep='.$i : route('subscription.index') }}"
               class="sidebar-ep {{ $active ? 'active' : '' }} {{ !$canWatch ? 'locked' : '' }}">
              Tập {{ $i }}
              @if(!$canWatch) <span>🔒</span> @endif
              @if($active) <span class="now-playing">▶</span> @endif
            </a>
          @endfor
        </div>
      @endif
    </div>
  </div>

</div>

@push('styles')
<style>
.watch-page { padding-top: 76px; }
.watch-layout { display: grid; grid-template-columns: 1fr 260px; gap: 24px; align-items: start; }
.watch-main { min-width: 0; }
.player-wrap {
  position: relative; background: #000; border-radius: 12px; overflow: hidden;
  aspect-ratio: 9/16; max-height: 78vh; margin-bottom: 16px;
}
.video-player { width: 100%; height: 100%; object-fit: contain; }
.player-error {
  width: 100%; height: 100%; display: flex; flex-direction: column;
  align-items: center; justify-content: center; color: var(--text2);
}
.player-error-icon { font-size: 48px; margin-bottom: 12px; }
.watch-title { font-size: 18px; font-weight: 600; margin-bottom: 12px; }
.ep-nav { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
.btn-ep { padding: 8px 18px; border: 1px solid var(--border); border-radius: 20px; font-size: 13px; color: var(--text2); transition: all .2s; }
.btn-ep:hover { border-color: var(--brand); color: var(--brand); }
.btn-ep-next { background: var(--brand); border-color: var(--brand); color: white; }
.btn-ep-next:hover { background: var(--brand2); color: white; }
.btn-ep-vip  { background: #f59e0b22; border-color: var(--vip); color: var(--vip); }
.free-notice { font-size: 12px; color: var(--text3); margin-top: 6px; }
.free-notice a { color: var(--vip); }
.sidebar-title { font-size: 14px; font-weight: 600; margin-bottom: 10px; }
.sidebar-eps { display: flex; flex-direction: column; gap: 4px; max-height: 70vh; overflow-y: auto; }
.sidebar-ep {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 12px; border-radius: 8px; font-size: 13px; color: var(--text2);
  background: var(--bg2); border: 1px solid transparent; transition: all .2s;
}
.sidebar-ep:hover { border-color: var(--border); color: var(--text); }
.sidebar-ep.active { border-color: var(--brand); color: var(--brand); background: rgba(229,57,53,.1); }
.sidebar-ep.locked { opacity: 0.5; }
.now-playing { color: var(--brand); font-size: 11px; }
@media(max-width: 900px) { .watch-layout { grid-template-columns: 1fr; } .watch-sidebar { display: none; } }
</style>
@endpush

@push('scripts')
<script>
// Lưu vị trí xem
const player = document.getElementById('player');
if (player) {
  const key = 'watch_{{ $id }}_ep{{ $episode }}';
  const saved = parseFloat(localStorage.getItem(key));
  if (saved > 5) {
    player.addEventListener('loadedmetadata', () => { player.currentTime = saved; }, { once: true });
  }
  player.addEventListener('timeupdate', () => {
    if (Math.floor(player.currentTime) % 5 === 0) localStorage.setItem(key, player.currentTime);
  });
  // Tự động chuyển tập tiếp
  player.addEventListener('ended', () => {
    const nextBtn = document.querySelector('.btn-ep-next');
    if (nextBtn) setTimeout(() => { window.location = nextBtn.href; }, 2000);
  });
}
</script>
@endpush
@endsection
