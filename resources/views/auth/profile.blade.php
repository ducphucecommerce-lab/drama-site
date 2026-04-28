@extends('layouts.app')
@section('title', 'Hồ sơ của tôi')

@section('content')
<div class="container" style="max-width:800px;padding-top:80px;margin:0 auto;padding-left:24px;padding-right:24px">
  <h1 style="font-size:22px;margin-bottom:24px">👤 Hồ sơ của tôi</h1>

  {{-- VIP Status card --}}
  <div class="profile-card" style="margin-bottom:20px">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
      <div>
        <div style="font-size:18px;font-weight:600">{{ $user->name }}</div>
        <div style="font-size:13px;color:var(--text2)">{{ $user->email }}</div>
        <div style="margin-top:8px">
          @if($user->isVip())
            <span class="vip-badge" style="font-size:13px;padding:4px 12px">
              ✅ VIP — hết hạn {{ $user->vip_expires_at->format('d/m/Y') }}
            </span>
          @else
            <span style="font-size:13px;color:var(--text3)">Tài khoản miễn phí</span>
          @endif
        </div>
      </div>
      @if(!$user->isVip())
        <a href="{{ route('subscription.index') }}" class="btn-vip-large">🌟 Nâng cấp VIP</a>
      @endif
    </div>
  </div>

  {{-- Watch history --}}
  @if($history->isNotEmpty())
    <div class="profile-card" style="margin-bottom:20px">
      <h2 style="font-size:16px;margin-bottom:14px">🕐 Lịch sử xem gần đây</h2>
      <div style="display:flex;flex-direction:column;gap:10px">
        @foreach($history as $h)
          <a href="{{ route('films.detail', $h->drama_id) }}?platform={{ $h->platform }}"
             style="display:flex;gap:12px;align-items:center;padding:10px;background:var(--bg3);border-radius:8px">
            @if($h->cover_url)
              <img src="{{ $h->cover_url }}" style="width:44px;height:62px;object-fit:cover;border-radius:6px">
            @endif
            <div>
              <div style="font-size:13px;font-weight:500">{{ $h->drama_title ?: 'Phim ngắn' }}</div>
              <div style="font-size:11px;color:var(--text3);margin-top:3px">
                {{ strtoupper($h->platform) }} · {{ $h->updated_at->diffForHumans() }}
              </div>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- Subscription history --}}
  @if($subs->isNotEmpty())
    <div class="profile-card">
      <h2 style="font-size:16px;margin-bottom:14px">💳 Lịch sử thanh toán</h2>
      <table class="tx-table">
        <thead><tr><th>Ngày</th><th>Phương thức</th><th>Số tiền</th><th>Trạng thái</th></tr></thead>
        <tbody>
          @foreach($subs as $s)
            <tr>
              <td>{{ $s->created_at->format('d/m/Y') }}</td>
              <td>{{ strtoupper($s->payment_method) }}</td>
              <td>@if($s->currency==='VND'){{ number_format($s->amount) }}đ @else ${{ $s->amount }} @endif</td>
              <td><span class="status-badge status-{{ $s->status }}">{{ $s->status }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

@push('styles')
<style>
.profile-card { background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:20px 24px }
</style>
@endpush
@endsection
