@extends('layouts.app')
@section('title', 'Quản lý Users')

@section('content')
<div class="container admin-page">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <h1 class="admin-title">👥 Quản lý Users</h1>
    <a href="{{ route('admin.index') }}" class="btn-outline">← Dashboard</a>
  </div>

  {{-- Search --}}
  <form method="GET" class="admin-search-form">
    <input type="text" name="q" value="{{ $search }}" placeholder="Tìm theo email hoặc tên...">
    <button type="submit">Tìm</button>
  </form>

  <table class="admin-table">
    <thead>
      <tr><th>ID</th><th>Tên</th><th>Email</th><th>VIP</th><th>Hết hạn</th><th>Ngày đăng ký</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
      @foreach($users as $user)
        <tr>
          <td>{{ $user->id }}</td>
          <td>{{ $user->name }} @if($user->is_admin)<span class="admin-badge">Admin</span>@endif</td>
          <td>{{ $user->email }}</td>
          <td>
            @if($user->isVip())
              <span class="vip-badge">VIP ✅</span>
            @else
              <span style="color:#888">Free</span>
            @endif
          </td>
          <td>{{ $user->vip_expires_at?->format('d/m/Y') ?? '—' }}</td>
          <td>{{ $user->created_at->format('d/m/Y') }}</td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              {{-- Tặng VIP --}}
              <form action="{{ route('admin.grant-vip', $user) }}" method="POST" style="display:flex;gap:4px">
                @csrf
                <select name="days" style="font-size:12px;padding:3px">
                  <option value="7">7 ngày</option>
                  <option value="30" selected>30 ngày</option>
                  <option value="90">90 ngày</option>
                  <option value="365">1 năm</option>
                </select>
                <button type="submit" class="btn-sm btn-green">Tặng VIP</button>
              </form>
              {{-- Thu hồi VIP --}}
              @if($user->isVip())
                <form action="{{ route('admin.revoke-vip', $user) }}" method="POST">
                  @csrf
                  <button type="submit" class="btn-sm btn-red"
                    onclick="return confirm('Thu hồi VIP của {{ $user->name }}?')">Thu hồi</button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div style="margin-top:16px">{{ $users->appends(request()->query())->links() }}</div>
</div>
@endsection
