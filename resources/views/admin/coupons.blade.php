@extends('layouts.app')
@section('title', 'Manage Coupons - ' . config('app.name'))
@section('content')
<div class="admin-wrap">
  <h1 class="admin-title">Coupon Management</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
  @endif

  {{-- Create Coupon --}}
  <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;margin-bottom:24px">
    <h2 style="font-size:15px;font-weight:600;color:#fff;margin-bottom:16px">Create New Coupon</h2>
    <form action="{{ route('admin.coupons.create') }}" method="POST">
      @csrf
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:14px">
        <div class="form-group" style="margin:0">
          <label class="form-label">Code</label>
          <input type="text" name="code" class="form-input" placeholder="SAVE20" required style="text-transform:uppercase">
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Type</label>
          <select name="discount_type" class="form-input platform-select" style="width:100%;max-width:100%">
            <option value="percent">Percent (%)</option>
            <option value="fixed">Fixed ($)</option>
          </select>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Value</label>
          <input type="number" name="discount_value" class="form-input" placeholder="20" step="0.01" min="0" required>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Max Uses (blank = unlimited)</label>
          <input type="number" name="max_uses" class="form-input" placeholder="100" min="1">
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Expires At (blank = never)</label>
          <input type="date" name="expires_at" class="form-input">
        </div>
      </div>
      <button type="submit" class="btn-submit" style="width:auto;padding:9px 24px">Create Coupon</button>
    </form>
  </div>

  {{-- Coupon List --}}
  <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
    <table class="data-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Type</th>
          <th>Value</th>
          <th>Used</th>
          <th>Max Uses</th>
          <th>Expires</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($coupons as $coupon)
        <tr>
          <td style="font-weight:600;color:#fff;font-family:monospace">{{ $coupon->code }}</td>
          <td>{{ $coupon->discount_type }}</td>
          <td>{{ $coupon->discount_type === 'percent' ? $coupon->discount_value . '%' : '$' . $coupon->discount_value }}</td>
          <td>{{ $coupon->used_count }}</td>
          <td>{{ $coupon->max_uses ?? 'unlimited' }}</td>
          <td>{{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Never' }}</td>
          <td>
            <span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;
              background:{{ $coupon->is_active ? 'rgba(52,211,153,0.15)' : 'rgba(239,68,68,0.15)' }};
              color:{{ $coupon->is_active ? '#34d399' : '#f87171' }}">
              {{ $coupon->is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td style="display:flex;gap:8px">
            <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST">
              @csrf
              <button type="submit" style="background:rgba(167,139,250,0.1);border:1px solid rgba(167,139,250,0.3);color:var(--purple);border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer">
                {{ $coupon->is_active ? 'Disable' : 'Enable' }}
              </button>
            </form>
            <form action="{{ route('admin.coupons.delete', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?')">
              @csrf @method('DELETE')
              <button type="submit" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer">
                Delete
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text3)">No coupons yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:20px">
    <a href="{{ route('admin.index') }}" style="color:var(--purple);font-size:13px">&larr; Back to Dashboard</a>
  </div>
</div>
@endsection
