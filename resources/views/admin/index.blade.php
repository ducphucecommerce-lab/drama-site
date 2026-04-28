@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="container admin-page">
  <h1 class="admin-title">🛠 Admin Dashboard</h1>

  {{-- Stats --}}
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-num">{{ number_format($stats['users']) }}</div>
      <div class="stat-label">Tổng users</div>
    </div>
    <div class="stat-card vip">
      <div class="stat-num">{{ number_format($stats['vip_users']) }}</div>
      <div class="stat-label">VIP đang hoạt động</div>
    </div>
    <div class="stat-card revenue">
      <div class="stat-num">{{ number_format($stats['revenue_vnd']) }}đ</div>
      <div class="stat-label">Doanh thu VNPay</div>
    </div>
    <div class="stat-card revenue-usd">
      <div class="stat-num">${{ number_format($stats['revenue_usd'], 2) }}</div>
      <div class="stat-label">Doanh thu Stripe</div>
    </div>
    <div class="stat-card today">
      <div class="stat-num">{{ $stats['today_payments'] }}</div>
      <div class="stat-label">Giao dịch hôm nay</div>
    </div>
  </div>

  {{-- Quick links --}}
  <div class="admin-nav">
    <a href="{{ route('admin.users') }}" class="admin-link">👥 Quản lý Users</a>
    <a href="{{ route('admin.transactions') }}" class="admin-link">💳 Giao dịch</a>
    <a href="{{ route('home') }}" class="admin-link">🎬 Xem trang web</a>
  </div>

  {{-- Recent transactions --}}
  <div class="admin-section">
    <h2>Giao dịch gần đây</h2>
    <table class="admin-table">
      <thead>
        <tr>
          <th>User</th><th>Phương thức</th><th>Số tiền</th>
          <th>Trạng thái</th><th>Thời gian</th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentTransactions as $tx)
          <tr>
            <td>{{ $tx->user->name }}<br><small>{{ $tx->user->email }}</small></td>
            <td>{{ strtoupper($tx->payment_method) }}</td>
            <td>
              @if($tx->currency === 'VND') {{ number_format($tx->amount) }}đ
              @else ${{ $tx->amount }} @endif
            </td>
            <td><span class="status-badge status-{{ $tx->status }}">{{ $tx->status }}</span></td>
            <td>{{ $tx->created_at->format('d/m H:i') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <a href="{{ route('admin.transactions') }}" class="btn-outline" style="margin-top:12px;display:inline-block">Xem tất cả →</a>
  </div>
</div>
@endsection
