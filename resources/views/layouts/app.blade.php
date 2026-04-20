<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name'))</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>

{{-- NAVBAR --}}
<nav class="navbar">
  <div class="nav-inner">
    <a href="{{ route('home') }}" class="logo">
      🎬 <span class="logo-text">{{ config('app.name', 'DramaStream') }}</span>
    </a>

    <div class="nav-center">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') && !request('tab') ? 'active' : '' }}">
        {{ __('nav.home') }}
      </a>
      <a href="{{ route('home', array_merge(request()->query(), ['tab' => 'trending'])) }}"
         class="{{ request('tab') === 'trending' ? 'active' : '' }}">
        {{ __('nav.trending') }}
      </a>
      <a href="{{ route('films.genre', 'romance') }}" class="{{ request()->routeIs('films.genre') && request()->route('genre') === 'romance' ? 'active' : '' }}">
        {{ __('nav.romance') }}
      </a>
      <a href="{{ route('films.genre', 'action') }}" class="{{ request()->routeIs('films.genre') && request()->route('genre') === 'action' ? 'active' : '' }}">
        {{ __('nav.action') }}
      </a>
    </div>

    <div class="nav-right">
      {{-- Search --}}
      <form action="{{ route('films.search') }}" method="GET" class="nav-search">
        <input type="text" name="q" placeholder="{{ __('nav.search_placeholder') }}" value="{{ request('q') }}">
        <button type="submit">🔍</button>
      </form>

      {{-- Platform select --}}
      <select class="platform-select" onchange="location.href='{{ route('home') }}?platform='+this.value">
        @foreach($platforms ?? [] as $key => $name)
          <option value="{{ $key }}" {{ request('platform', 'shortmax') === $key ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>

      {{-- Language switcher --}}
      <div class="lang-switcher">
        <div class="lang-btn">
          @php
            $langs = ['en'=>'🇬🇧 EN','vi'=>'🇻🇳 VI','id'=>'🇮🇩 ID','th'=>'🇹🇭 TH','ar'=>'🇸🇦 AR'];
            $cur = session('lang', 'en');
          @endphp
          {{ $langs[$cur] ?? '🌐 EN' }} ▾
        </div>
        <div class="lang-dropdown">
          @foreach($langs as $code => $label)
            <a href="{{ route('home', array_merge(request()->query(), ['lang' => $code])) }}"
               class="lang-option {{ $cur === $code ? 'active' : '' }}"
               onclick="fetch('/lang/{{ $code }}');return true;">
              {{ $label }}
            </a>
          @endforeach
        </div>
      </div>

      {{-- Auth --}}
      @auth
        <div class="user-menu">
          <span class="user-name">
            {{ auth()->user()->name }}
            @if(auth()->user()->isVip())
              <span class="vip-badge">VIP</span>
            @endif
            ▾
          </span>
          <div class="dropdown">
            <a href="{{ route('profile') }}">{{ __('nav.profile') }}</a>
            @if(auth()->user()->is_admin)
              <a href="{{ route('admin.index') }}">Admin</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit">{{ __('nav.logout') }}</button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="btn-outline">{{ __('nav.login') }}</a>
        <a href="{{ route('subscription.index') }}" class="btn-vip">✦ VIP</a>
      @endauth
    </div>
  </div>
</nav>

{{-- Language strip --}}
@php $langs2 = ['en'=>'🇬🇧 EN','vi'=>'🇻🇳 VI','id'=>'🇮🇩 ID','th'=>'🇹🇭 TH','ar'=>'🇸🇦 AR']; $curLang = session('lang','en'); @endphp
<div class="lang-strip">
  <span class="lang-strip-label">{{ __('nav.language') }}:</span>
  <div class="lang-chips">
    @foreach($langs2 as $code => $label)
      <a href="?{{ http_build_query(array_merge(request()->query(), ['lang' => $code])) }}"
         class="lang-chip {{ $curLang === $code ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
  </div>
</div>

{{-- Flash messages --}}
<div style="max-width:1400px;margin:0 auto;padding:12px 28px 0">
  @foreach(['success','error','warning'] as $type)
    @if(session($type))
      <div class="alert alert-{{ $type }}">{{ session($type) }}</div>
    @endif
  @endforeach
</div>

{{-- Content --}}
<main>@yield('content')</main>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">🎬 {{ config('app.name') }}</div>
    <p>{{ __('footer.desc') }}</p>
    <div class="footer-links">
      <a href="{{ route('home') }}">{{ __('nav.home') }}</a>
      <a href="{{ route('films.search') }}">{{ __('nav.search') }}</a>
      <a href="{{ route('subscription.index') }}">VIP</a>
    </div>
    <p class="footer-copy">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
  </div>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
