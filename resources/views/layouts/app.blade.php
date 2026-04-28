<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name'))</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
  <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"></noscript>
  <link rel="preload" href="{{ asset('css/app.css') }}?v=10" as="style">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=10">
  @stack('styles')
</head>
<body>
<!-- Loading Screen -->
<div id="page-loader" style="position:fixed;top:0;left:0;width:100%;height:100%;background:#0a0a0f;display:flex;align-items:center;justify-content:center;z-index:99999;transition:opacity 0.5s,visibility 0.5s;">
  <div style="display:flex;flex-direction:column;align-items:center;gap:20px;">
    <div style="font-size:28px;font-weight:700;background:linear-gradient(135deg,#a855f7,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">▶ DramaGo</div>
    <div style="width:50px;height:50px;border:3px solid rgba(168,85,247,0.2);border-top-color:#a855f7;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
    <div style="color:rgba(255,255,255,0.5);font-size:13px;">Dang tai...</div>
  </div>
</div>
<style>@keyframes spin{to{transform:rotate(360deg)}}#page-loader.hidden{opacity:0;visibility:hidden;pointer-events:none;}</style>
<script>
function hideLoader(){var e=document.getElementById("page-loader");if(e)e.classList.add("hidden")}
document.addEventListener("DOMContentLoaded",hideLoader);
window.addEventListener("load",hideLoader);
window.setTimeout(hideLoader,5000);
</script>


<nav class="navbar">
  <div class="nav-inner">
    <a href="{{ route('home') }}?platform={{ request('platform','shortmax') }}" class="logo">
      <span class="logo-icon">&#9654;</span>
      <span class="logo-text">{{ config('app.name', 'DramaGo') }}</span>
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
            @if(auth()->user()->isVip()) <span class="vip-badge">VIP</span> @php session()->forget("show_vip_trial"); @endphp
@endif
            &#9660;
          </span>
          <div class="dropdown">
            <a href="{{ route('profile') }}">{{ app()->getLocale() === 'vi' ? 'Ho so' : 'Profile' }}</a>
            @if(auth()->user()->is_admin)
              <a href="{{ route('admin.index') }}">Admin</a>
            @php session()->forget("show_vip_trial"); @endphp
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
    @php session()->forget("show_vip_trial"); @endphp
@endif
  @endforeach
</div>

<main>@yield('content')</main>

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
{{-- VIP Trial Popup --}}
@if(session('show_vip_trial'))
<div id="vipPopup" style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.75);backdrop-filter:blur(4px)">
  <div style="background:linear-gradient(135deg,#1a1a2e,#0d0d1a);border:1px solid rgba(167,139,250,0.4);border-radius:24px;padding:36px 32px;max-width:420px;width:90%;text-align:center;box-shadow:0 32px 80px rgba(0,0,0,0.6);position:relative">
    <div style="width:64px;height:64px;background:linear-gradient(135deg,#a78bfa,#ec4899);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px">&#11088;</div>
    <h2 style="font-size:22px;font-weight:700;color:#fff;margin-bottom:8px">
      {{ app()->getLocale() === 'vi' ? 'Chao mung den DramaGo!' : 'Welcome to DramaGo!' }}
    </h2>
    <p style="font-size:14px;color:rgba(255,255,255,0.6);margin-bottom:20px;line-height:1.6">
      {{ app()->getLocale() === 'vi'
        ? 'Ban duoc tang goi VIP mien phi trong 30 phut! Hay trai nghiem xem phim khong gioi han ngay bay gio.'
        : 'You have received a FREE VIP trial for 30 minutes! Enjoy unlimited drama watching right now.' }}
    </p>
    <div style="background:rgba(167,139,250,0.1);border:1px solid rgba(167,139,250,0.25);border-radius:12px;padding:12px 20px;margin-bottom:20px">
      <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:4px">{{ app()->getLocale() === 'vi' ? 'Thoi gian con lai' : 'Time remaining' }}</div>
      <div id="vipTimer" style="font-size:28px;font-weight:700;background:linear-gradient(135deg,#a78bfa,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">30:00</div>
    </div>
    <div style="display:flex;gap:10px">
      <button onclick="closeVipPopup()" style="flex:1;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:11px;font-size:13px;color:rgba(255,255,255,0.6);cursor:pointer">
        {{ app()->getLocale() === 'vi' ? 'Xem phim ngay' : 'Start watching' }}
      </button>
      <a href="{{ route('subscription.index') }}" style="flex:1;background:linear-gradient(135deg,#a78bfa,#ec4899);border:none;border-radius:10px;padding:11px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;text-decoration:none;display:flex;align-items:center;justify-content:center">
        {{ app()->getLocale() === 'vi' ? 'Nang cap VIP' : 'Upgrade VIP' }}
      </a>
    </div>
  </div>
@php session()->forget('show_vip_trial'); @endphp
</div>
<script>
function closeVipPopup() {
  document.getElementById('vipPopup').style.display = 'none';
}
// Countdown timer 30 minutes
var total = 30 * 60;
var timer = setInterval(function() {
  total--;
  if (total <= 0) { clearInterval(timer); closeVipPopup(); return; }
  var m = Math.floor(total / 60);
  var s = total % 60;
  document.getElementById('vipTimer').textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
}, 1000);
// Close on backdrop click
document.getElementById('vipPopup').addEventListener('click', function(e) {
  if (e.target === this) closeVipPopup();
});
</script>
@php session()->forget("show_vip_trial"); @endphp
@endif
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>