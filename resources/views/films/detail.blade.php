@extends('layouts.app')
@section('title', ($film['title'] ?? 'Chi tiết phim') . ' - ' . config('app.name'))

@section('content')
<div class="container">
  <div class="film-detail">

    {{-- Breadcrumb --}}
    <div class="breadcrumb">
      <a href="{{ route('home') }}">Trang chủ</a>
      <span>/</span>
      <span>{{ Str::limit($film['title'] ?? 'Phim', 40) }}</span>
    </div>

    <div class="detail-layout">
      {{-- Cover --}}
      <div class="detail-cover">
        <img src="{{ $film['cover'] ?? $film['cover_url'] ?? asset('img/no-cover.png') }}"
             alt="{{ $film['title'] ?? '' }}">
      </div>

      {{-- Info --}}
      <div class="detail-info">
        <h1 class="detail-title">{{ $film['title'] ?? 'Phim ngắn' }}</h1>

        @if(isset($film['genre']))
          <div class="detail-tags">
            @foreach((array)$film['genre'] as $g)
              <a href="{{ route('films.genre', $g) }}" class="genre-tag">{{ $g }}</a>
            @endforeach
          </div>
        @endif

        <div class="detail-meta">
          @if(isset($film['episodes'])) <span>📺 {{ $film['episodes'] }} tập</span> @endif
          @if(isset($film['year']))     <span>📅 {{ $film['year'] }}</span> @endif
          @if(isset($film['rating']))   <span>⭐ {{ $film['rating'] }}</span> @endif
          <span>🔖 {{ strtoupper($platform) }}</span>
        </div>

        @if(isset($film['description']))
          <p class="detail-desc">{{ $film['description'] }}</p>
        @endif

        {{-- Watch button --}}
        <div class="detail-actions">
          <a href="{{ route('films.watch', $id) }}?platform={{ $platform }}&ep=1"
             class="btn-watch-large">▶ Xem tập 1</a>

          @auth
            @if(!auth()->user()->isVip())
              <a href="{{ route('subscription.index') }}" class="btn-vip-outline">🌟 Mua VIP xem full</a>
            @endif
          @else
            <a href="{{ route('subscription.index') }}" class="btn-vip-outline">🌟 VIP — xem không giới hạn</a>
          @endauth
        </div>

        {{-- Episode list --}}
        @if(isset($film['episode_list']) && is_array($film['episode_list']))
          <div class="episode-list">
            <h3>Danh sách tập</h3>
            <div class="ep-grid">
              @foreach($film['episode_list'] as $ep)
                @php
                  $epNum  = $ep['episode'] ?? $ep['ep'] ?? $loop->iteration;
                  $isFree = $epNum <= 3;
                  $isVip  = auth()->check() && auth()->user()->isVip();
                  $canWatch = $isFree || $isVip;
                @endphp
                <a href="{{ $canWatch ? route('films.watch', $id).'?platform='.$platform.'&ep='.$epNum : route('subscription.index') }}"
                   class="ep-item {{ $canWatch ? '' : 'ep-locked' }}">
                  {{ $epNum }}
                  @if(!$isFree && !$isVip) <span class="lock-icon">🔒</span> @endif
                </a>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>

  </div>
</div>

@push('styles')
<style>
.breadcrumb { display:flex;gap:8px;align-items:center;font-size:13px;color:var(--text3);margin-bottom:20px }
.breadcrumb a:hover { color:var(--brand) }
.detail-layout { display:grid;grid-template-columns:260px 1fr;gap:32px;align-items:start }
.detail-cover img { width:100%;border-radius:12px;aspect-ratio:9/16;object-fit:cover }
.detail-title { font-size:24px;font-weight:700;margin-bottom:12px;line-height:1.3 }
.detail-tags { display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px }
.genre-tag { padding:4px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:16px;font-size:12px;color:var(--text2) }
.genre-tag:hover { border-color:var(--brand);color:var(--brand) }
.detail-meta { display:flex;flex-wrap:wrap;gap:14px;font-size:13px;color:var(--text2);margin-bottom:16px }
.detail-desc { font-size:13px;color:var(--text2);line-height:1.7;margin-bottom:20px }
.detail-actions { display:flex;flex-wrap:wrap;gap:10px;margin-bottom:24px }
.btn-watch-large { padding:12px 28px;background:var(--brand);color:white;border-radius:24px;font-size:15px;font-weight:600 }
.btn-watch-large:hover { background:var(--brand2) }
.btn-vip-outline { padding:12px 24px;border:2px solid var(--vip);color:var(--vip);border-radius:24px;font-size:14px;font-weight:600 }
.episode-list h3 { font-size:15px;margin-bottom:12px }
.ep-grid { display:flex;flex-wrap:wrap;gap:8px }
.ep-item { width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:var(--bg3);border:1px solid var(--border);border-radius:8px;font-size:13px;position:relative;transition:all .2s }
.ep-item:hover { border-color:var(--brand);color:var(--brand) }
.ep-locked { opacity:0.5;cursor:not-allowed }
.lock-icon { position:absolute;top:-4px;right:-4px;font-size:10px }
@media(max-width:700px){ .detail-layout{grid-template-columns:1fr} .detail-cover{max-width:220px;margin:0 auto} }
</style>
@endpush
@endsection
