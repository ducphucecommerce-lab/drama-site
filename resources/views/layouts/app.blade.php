<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name'))</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=10">
  @stack('styles')
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="{{ route('home') }}?platform={{ request('platform','shortmax') }}" class="logo">
      <span class="logo-icon">&#9654;</span>
      <span class="logo-text">{{ config('app.name', 'DramaSnap') }}</span>
    </a>

    <div class="nav-right">
      <form action="{{ route('films.search') }}" method="GET" class="nav-search">
        <input type="text" name="q" placeholder="{{ app()->getLocale() === 'vi' ? 'Tim phim...' : 'Search dramas...' }}" value="{{ request('q') }}">
        <button type="submit">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </button>
      </form>

      <select class="platform-select" onchange="location.href='/?platform='+this.value">
        @foreach($platforms ?? [] as $key => $name)
          <option value="{{ $key }}" {{ request('platform','shortmax') === $key ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>

      <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'vi' ? 'en' : 'vi']) }}"
         class="lang-toggle">
        {{ app()->getLocale() === 'vi' ? 'EN' : 'VI' }}
      </a>

      @auth
        <div class="user-menu">
          <span class="user-name">
            {{ Str::limit(auth()->user()->name, 10) }}
            @if(auth()->user()->isVip()) <span class="vip-badge">VIP</span> @endif
            &#9660;
          </span>
          <div class="dropdown">
            <a href="{{ route('profile') }}">{{ app()->getLocale() === 'vi' ? 'Ho so' : 'Profile' }}</a>
            @if(auth()->user()->is_admin)
              <a href="{{ route('admin.index') }}">Admin</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit">{{ app()->getLocale() === 'vi' ? 'Dang xuat' : 'Logout' }}</button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="btn-outline">{{ app()->getLocale() === 'vi' ? 'Dang nhap' : 'Sign in' }}</a>
        <a href="{{ route('subscription.index') }}" class="btn-vip">VIP</a>
      @endauth
    </div>
  </div>
</nav>

<div style="max-width:1400px;margin:0 auto;padding:8px 24px 0">
  @foreach(['success','error','warning'] as $type)
    @if(session($type))
      <div class="alert alert-{{ $type }}">{{ session($type) }}</div>
    @endif
  @endforeach
</div>

<main style="margin-top:var(--nav-h)">@yield('content')</main>

<nav class="bottom-nav">
  <div class="bottom-nav-inner">
    <a href="{{ route('home') }}?platform={{ request('platform','shortmax') }}"
       class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
      </svg>
      {{ app()->getLocale() === 'vi' ? 'Trang chu' : 'Home' }}
    </a>
    <form action="{{ route('films.search') }}" method="GET" class="bottom-nav-search">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" name="q" placeholder="{{ app()->getLocale() === 'vi' ? 'Tim phim...' : 'Search...' }}">
    </form>
    <a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'vi' ? 'en' : 'vi']) }}"
       class="bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
        <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
      </svg>
      {{ app()->getLocale() === 'vi' ? 'EN' : 'VI' }}
    </a>
    @auth
    <a href="{{ route('profile') }}" class="bottom-nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
      </svg>
      {{ app()->getLocale() === 'vi' ? 'Ho so' : 'Profile' }}
    </a>
    @else
    <a href="{{ route('login') }}" class="bottom-nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
        <polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
      </svg>
      {{ app()->getLocale() === 'vi' ? 'Dang nhap' : 'Sign in' }}
    </a>
    @endauth
  </div>
</nav>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">{{ config('app.name') }}</div>
    <p>{{ app()->getLocale() === 'vi' ? 'Xem phim ngan tu nhieu nen tang' : 'Short dramas from multiple platforms' }}</p>
    <div class="footer-links">
      <a href="{{ route('home') }}?platform=shortmax">{{ app()->getLocale() === 'vi' ? 'Trang chu' : 'Home' }}</a>
      <a href="{{ route('films.search') }}">{{ app()->getLocale() === 'vi' ? 'Tim kiem' : 'Search' }}</a>
      <a href="{{ route('subscription.index') }}">VIP</a>
    </div>
    <p class="footer-copy">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
  </div>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>