@extends('layouts.app')
@section('title', 'Đăng ký')

@section('content')
<div class="auth-page">
  <div class="auth-box">
    <h1>🎬 Đăng ký miễn phí</h1>
    <p>Tạo tài khoản để xem 3 tập đầu mỗi phim miễn phí. Nâng VIP để xem không giới hạn!</p>

    <form method="POST" action="{{ route('register') }}">
      @csrf
      <div class="form-group">
        <label>Họ tên</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nguyễn Văn A" required>
        @error('name')<div class="error-msg">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required>
        @error('email')<div class="error-msg">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Mật khẩu</label>
        <input type="password" name="password" placeholder="Tối thiểu 6 ký tự" required>
        @error('password')<div class="error-msg">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Xác nhận mật khẩu</label>
        <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
      </div>
      <button type="submit" class="btn-submit">Đăng ký ngay</button>
    </form>

    <div class="auth-link">
      Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
    </div>
  </div>
</div>
@endsection
