@extends('layouts.app')
@section('title', __('home.title') . ' - ' . config('app.name'))

@section('content')

{{-- Hero Section --}}
<div class="hero-section">
  <div class="hero-card">
    <div>
      <div class="hero-badge">
        <div class="hero-badge-dot"></div>
        {{ __('home.featured_today') }}
      </div>
      <h1 class="hero-title">{{ __('home.hero_title_1') }} <em>{{ __('home.hero_title_em') }}</em><br>{{ __('home.hero_title_2') }}</h1>
      <p class="hero-desc">{{ __('home.hero_desc') }}</p>
      <div class="hero-actions">
        <a href="{{ route('home', ['tab' => 'trending', 'platform' => request('platform','freereels')]) }}" class="btn-watch">
          <div style="width:0;height:0;border-style:solid;border-width:6px 0 6px 11px;border-color:transparent transparent transparent #fff"></div>
          {{ __('home.watch_now') }}
        </a>
        <a href="{{ route('films.search') }}" class="btn-browse">{{ __('home.browse_all') }}</a>
      </div>
      <div class="hero-stats">
        <div><div class="hero-stat-num">22+</div><div class="hero-stat-label">{{ __('home.stat_platforms') }}</div></div>
        <div class="hero-stat-divider"></div>
        <div><div class="hero-stat-num">10K+</div><div class="hero-stat-label">{{ __('home.stat_episodes') }}</div></div>
        <div class="hero-stat-divider"></div>
        <div><div class="hero-stat-num">5</div><div class="hero-stat-label">{{ __('home.stat_languages') }}</div></div>
      </div>
    </div>
    <div class="hero-cover-wrap">
      @if(!empty($films[0]))
        <div class="hero-cover-img">
          <img src="{{ $films[0]['cover'] ?? '' }}" alt="{{ $films[0]['title'] ?? '' }}" loading="lazy">
        </div>
        <div class="hero-cover-overlay">
          <div>
            <div class="hero-cover-title">{{ Str::limit($films[0]['title'] ?? '', 40) }}</div>
            <div class="hero-cover-meta">{{ $platform ?? '' }} · {{ $films[0]['chapters'] ?? $films[0]['episodes'] ?? '?' }} {{ __('home.episodes') }}</div>
          </div>
          <div class="hero-cover-ep">▶ EP 1</div>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Platform Pills --}}
<div class="platform-section">
  <div class="platform-pills">
    <a href="{{ route('home', array_merge(request()->query(), ['platform' => 'freereels'])) }}"
       class="platform-pill {{ request('platform','freereels') === 'freereels' ? 'active' : '' }}">FreeReels</a>
    @foreach($platforms ?? [] as $key => $name)
      <a href="{{ route('home', array_merge(request()->query(), ['platform' => $key])) }}"
         class="platform-pill {{ request('platform') === $key ? 'active' : '' }}">{{ $name }}</a>
    @endforeach
  </div>
</div>

{{-- Main content --}}
<div class="section">

  {{-- VIP Banner --}}
  @auth
    @if(!auth()->user()->isVip())
      <div class="vip-banner">
        <div>
          <h3>✦ {{ __('home.vip_banner_title') }}</h3>
          <p>{{ __('home.vip_banner_desc') }}</p>
        </div>
        <a href="{{ route('subscription.index') }}" class="btn-vip-large">{{ __('home.buy_vip') }}</a>
      </div>
    @endif
  @else
    <div class="vip-banner">
      <div>
        <h3>🎬 {{ __('home.register_banner_title') }}</h3>
        <p>{{ __('home.register_banner_desc') }}</p>
      </div>
      <a href="{{ route('register') }}" class="btn-outline-light">{{ __('home.register_free') }}</a>
    </div>
  @endauth

  {{-- Tabs --}}
  <div class="tabs">
    <a href="{{ route('home', array_merge(request()->query(), ['tab' => 'recommend'])) }}"
       class="tab {{ request('tab','recommend') === 'recommend' ? 'active' : '' }}">
       🔥 {{ __('home.tab_recommend') }}
    </a>
    <a href="{{ route('home', array_merge(request()->query(), ['tab' => 'trending'])) }}"
       class="tab {{ request('tab') === 'trending' ? 'active' : '' }}">
       📈 {{ __('home.tab_trending') }}
    </a>
  </div>

  {{-- Section header --}}
  <div class="section-header">
    <div class="section-title">
      <div class="section-title-bar"></div>
      {{ request('tab') === 'trending' ? __('home.tab_trending') : __('home.tab_recommend') }}
    </div>
  </div>

  {{-- Film grid --}}
  @if(empty($films))
    <div class="empty-state">
      <p>😕 {{ __('home.no_films') }}</p>
    </div>
  @else
    <div class="film-grid">
      @foreach($films as $film)
        @php $fid = $film['id'] ?? $film['drama_id'] ?? '#'; @endphp
        <a href="{{ route('films.detail', $fid) }}?platform={{ request('platform','freereels') }}&lang={{ session('lang','en') }}"
           class="film-card">
          <div class="card-thumb">
            <img src="{{ $film['cover'] ?? $film['cover_url'] ?? '' }}"
                 alt="{{ $film['title'] ?? '' }}" loading="lazy"
                 onerror="this.style.display='none'">
            <div class="card-overlay">
              <div class="play-circle"><div class="play-tri"></div></div>
            </div>
            <span class="free-badge">3 {{ __('home.free_eps') }}</span>
          </div>
          <div class="card-info">
            <p class="card-title">{{ $film['title'] ?? __('home.unknown') }}</p>
            <div class="card-meta">
              <span class="card-platform-tag">{{ request('platform','freereels') }}</span>
              @if(isset($film['chapters']) || isset($film['episodes']))
                <span class="card-eps-count">{{ $film['chapters'] ?? $film['episodes'] }} ep</span>
              @endif
            </div>
          </div>
        </a>
      @endforeach
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrap">
      @if($page > 1)
        <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" class="btn-page">← {{ __('home.prev') }}</a>
      @endif
      <span>{{ __('home.page') }} {{ $page }}</span>
      <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" class="btn-page">{{ __('home.next') }} →</a>
    </div>
  @endif

</div>
@endsection
