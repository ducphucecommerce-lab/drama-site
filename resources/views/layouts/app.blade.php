<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name'))</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>

{{-- NAVBAR --}}
<nav class="navbar">
  <div class="nav-inner">
    <a href="{{ route('home') }}" class="logo">🎬 {{ config('app.name') }}</a>

    <div class="nav-center">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Trang chủ</a>
      <a href="{{ route('home', ['tab' => 'trending']) }}" class="{{ request()->get('tab') === 'trending' ? 'active' : '' }}">Xu hướng</a>
      <a href="{{ route('films.genre', 'romance') }}">Tình cảm</a>
      <a href="{{ route('films.genre', 'action') }}">Hành động</a>
    </div>

    <div class="nav-right">
      {{-- Search --}}
      <form action="{{ route('films.search') }}" method="GET" class="nav-search">
        <input type="text" name="q" placeholder="Tìm phim..." value="{{ request('q') }}">
        <button type="submit">🔍</button>
      </form>

      {{-- Platform filter --}}
      <select class="platform-select" onchange="location.href='{{ route('home') }}?platform='+this.value">
        @foreach($platforms ?? [] as $key => $name)
          <option value="{{ $key }}" {{ request('platform', 'dramabox') === $key ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>

      {{-- Auth --}}
      @auth
        <div class="user-menu">
          <span class="user-name">
            {{ auth()->user()->name }}
            @if(auth()->user()->isVip())
              <span class="vip-badge">VIP</span>
            @endif
          </span>
          <div class="dropdown">
            <a href="{{ route('profile') }}">Hồ sơ</a>
            @if(auth()->user()->is_admin)
              <a href="{{ route('admin.index') }}">Admin</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit">Đăng xuất</button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="btn-outline">Đăng nhập</a>
        <a href="{{ route('subscription.index') }}" class="btn-vip">🌟 VIP</a>
      @endauth
    </div>
  </div>
</nav>

{{-- FLASH MESSAGES --}}
<div class="container" style="padding-top:70px">
  @foreach(['success','error','warning'] as $type)
    @if(session($type))
      <div class="alert alert-{{ $type }}">{{ session($type) }}</div>
    @endif
  @endforeach
</div>

{{-- CONTENT --}}
<main>
  @yield('content')
</main>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">🎬 {{ config('app.name') }}</div>
    <p>Xem phim ngắn từ 22+ nền tảng — DramaBox, ReelShort, NetShort và nhiều hơn nữa</p>
    <div class="footer-links">
      <a href="{{ route('home') }}">Trang chủ</a>
      <a href="{{ route('films.search') }}">Tìm kiếm</a>
      <a href="{{ route('subscription.index') }}">Gói VIP</a>
    </div>
    <p class="footer-copy">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
  </div>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
