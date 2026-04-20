@extends('layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div class="auth-page">
  <div class="auth-box">
    <h1>👋 Đăng nhập</h1>
    <p>Chào mừng trở lại! Đăng nhập để tiếp tục xem phim.</p>

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required>
        @error('email')<div class="error-msg">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Mật khẩu</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text2);cursor:pointer">
          <input type="checkbox" name="remember"> Nhớ đăng nhập
        </label>
      </div>
      <button type="submit" class="btn-submit">Đăng nhập</button>
    </form>

    <div class="auth-link">
      Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký miễn phí</a>
    </div>
  </div>
</div>
@endsection
