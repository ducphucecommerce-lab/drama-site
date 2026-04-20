@extends('layouts.app')
@section('title', ($film['title'] ?? 'Detail') . ' - ' . config('app.name'))

@section('content')
<div class="detail-wrap">

  {{-- Breadcrumb --}}
  <div class="breadcrumb">
    <a href="{{ route('home') }}">{{ __('nav.home') }}</a>
    <span>/</span>
    <span>{{ Str::limit($film['title'] ?? 'Film', 40) }}</span>
  </div>

  <div class="detail-grid">

    {{-- Cover --}}
    <div class="detail-cover">
      <img src="{{ $film['cover'] ?? $film['cover_url'] ?? '' }}"
           alt="{{ $film['title'] ?? '' }}" loading="lazy">
    </div>

    {{-- Info --}}
    <div class="detail-info">
      <h1 class="detail-title">{{ $film['title'] ?? 'Short Drama' }}</h1>

      <div class="detail-badges">
        @if(isset($film['status']))
          <span class="badge badge-status">{{ $film['status'] }}</span>
        @endif
        <span class="badge">{{ strtoupper($platform) }}</span>
        @if(isset($film['episodes']) || isset($film['total_episodes']))
          <span class="badge">{{ $film['total_episodes'] ?? $film['episodes'] }} eps</span>
        @endif
        @foreach(array_slice((array)($film['genres'] ?? []), 0, 3) as $g)
          @if($g)
            <span class="badge">{{ $g }}</span>
          @endif
        @endforeach
      </div>

      @if(isset($film['synopsis']) || isset($film['description']))
        <p class="detail-synopsis">{{ $film['synopsis'] ?? $film['description'] }}</p>
      @endif

      <div class="detail-actions">
        <a href="{{ route('films.watch', $id) }}?platform={{ $platform }}&ep=1&lang={{ session('lang','en') }}"
           class="btn-ep1">▶ Watch Episode 1</a>
        @auth
          @if(!auth()->user()->isVip())
            <a href="{{ route('subscription.index') }}" class="btn-browse">✦ Get VIP</a>
          @endif
        @else
          <a href="{{ route('subscription.index') }}" class="btn-browse">✦ Get VIP</a>
        @endauth
      </div>

      {{-- Episode list --}}
      @if(!empty($film['episode_list']))
        <div class="ep-section">
          <div class="ep-section-title">Episodes</div>
          <div class="ep-list">
            @foreach($film['episode_list'] as $ep)
              @php
                $epNum   = $ep['episode'] ?? $ep['index'] ?? $loop->iteration;
                $epId    = $ep['id'] ?? $epNum;
                $isFree  = $epNum <= 3;
                $isVip   = auth()->check() && auth()->user()->isVip();
                $canWatch = $isFree || $isVip;
              @endphp
              <a href="{{ $canWatch ? route('films.watch', $id).'?platform='.$platform.'&ep='.$epNum.'&lang='.session('lang','en') : route('subscription.index') }}"
                 class="ep-item {{ !$canWatch ? 'locked' : '' }}">
                <span>Episode {{ $epNum }}</span>
                @if(!$canWatch)
                  <span class="ep-lock">🔒</span>
                @else
                  <span class="ep-arrow">▶</span>
                @endif
              </a>
            @endforeach
          </div>
        </div>
      @endif
    </div>

  </div>
</div>
@endsection