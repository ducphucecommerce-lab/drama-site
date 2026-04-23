@extends('layouts.app')
@section('title', app()->getLocale() === 'vi' ? 'Dang ky - ' . config('app.name') : 'Register - ' . config('app.name'))

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">
      <span class="logo-icon">&#9654;</span>
      <span class="logo-text">{{ config('app.name') }}</span>
    </div>
    <h1 class="auth-title">{{ app()->getLocale() === 'vi' ? 'Tao tai khoan mien phi' : 'Create free account' }}</h1>
    <p class="auth-sub">{{ app()->getLocale() === 'vi' ? 'Xem 3 tap mien phi moi phim' : 'Watch 3 free episodes per drama' }}</p>

    @if($errors->any())
      <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('register') }}" method="POST">
      @csrf
      <div class="form-group">
        <label class="form-label">{{ app()->getLocale() === 'vi' ? 'Ho ten' : 'Full Name' }}</label>
        <input type="text" name="name" class="form-input" placeholder="{{ app()->getLocale() === 'vi' ? 'Nguyen Van A' : 'John Doe' }}" value="{{ old('name') }}" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-input" placeholder="your@email.com" value="{{ old('email') }}" required>
      </div>
      <div class="form-group">
        <label class="form-label">{{ app()->getLocale() === 'vi' ? 'Mat khau' : 'Password' }}</label>
        <input type="password" name="password" class="form-input" placeholder="••••••••" required>
      </div>
      <div class="form-group">
        <label class="form-label">{{ app()->getLocale() === 'vi' ? 'Xac nhan mat khau' : 'Confirm Password' }}</label>
        <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-submit">
        {{ app()->getLocale() === 'vi' ? 'Dang ky' : 'Create Account' }}
      </button>
    </form>

    <div class="auth-divider">
      <span>{{ app()->getLocale() === 'vi' ? 'Da co tai khoan?' : 'Already have an account?' }}</span>
    </div>

    <a href="{{ route('login') }}" class="btn-register">
      {{ app()->getLocale() === 'vi' ? 'Dang nhap' : 'Sign In' }}
    </a>
  </div>
</div>

@push('styles')
<style>
.auth-wrap{min-height:calc(100vh - var(--nav-h));display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(ellipse at top,rgba(167,139,250,0.08) 0%,transparent 60%)}
.auth-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-xl);padding:36px 32px;width:100%;max-width:400px;box-shadow:0 24px 60px rgba(0,0,0,0.4)}
.auth-logo{display:flex;align-items:center;gap:8px;justify-content:center;margin-bottom:20px}
.auth-logo .logo-icon{width:32px;height:32px;background:var(--grad);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff}
.auth-logo .logo-text{font-size:18px;font-weight:700;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.auth-title{font-size:22px;font-weight:700;color:#fff;margin-bottom:6px;text-align:center}
.auth-sub{font-size:13px;color:var(--text2);margin-bottom:24px;text-align:center}
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:12px;color:var(--text2);margin-bottom:5px;font-weight:500}
.form-input{width:100%;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);padding:11px 14px;font-size:14px;color:#fff;outline:none;transition:border-color .2s}
.form-input:focus{border-color:rgba(167,139,250,0.5);background:rgba(255,255,255,0.06)}
.form-input::placeholder{color:var(--text3)}
.btn-submit{width:100%;background:var(--grad);border:none;border-radius:var(--radius);padding:12px;font-size:14px;font-weight:600;color:#fff;cursor:pointer;transition:opacity .2s;margin-top:6px}
.btn-submit:hover{opacity:0.88}
.auth-divider{text-align:center;margin:18px 0 12px;font-size:13px;color:var(--text3)}
.btn-register{display:block;width:100%;text-align:center;background:transparent;border:1px solid var(--border2);border-radius:var(--radius);padding:11px;font-size:13px;font-weight:500;color:var(--text2);transition:all .2s;text-decoration:none}
.btn-register:hover{color:#fff;border-color:rgba(167,139,250,0.4);background:rgba(167,139,250,0.06)}
.alert{padding:10px 14px;border-radius:var(--radius);margin-bottom:14px;font-size:13px}
.alert-error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#f87171}
</style>
@endpush
@endsection