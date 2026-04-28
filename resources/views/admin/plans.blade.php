@extends('layouts.app')
@section('title', 'Manage VIP Plans - ' . config('app.name'))
@section('content')
<div class="admin-wrap">
  <h1 class="admin-title">VIP Plans Management</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="plan-grid" style="grid-template-columns:repeat(3,1fr)">
    @foreach($plans as $plan)
    <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }}">
      <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-group">
          <label class="form-label">Plan Name</label>
          <input type="text" name="name" value="{{ $plan->name }}" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Price (USD)</label>
          <input type="number" name="price" value="{{ $plan->price }}" step="0.01" min="0" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Duration (days)</label>
          <input type="number" name="days" value="{{ $plan->days }}" min="1" class="form-input" required>
        </div>
        <div style="display:flex;gap:16px;margin-bottom:14px">
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text2);cursor:pointer">
            <input type="checkbox" name="is_featured" value="1" {{ $plan->is_featured ? 'checked' : '' }}>
            Featured
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text2);cursor:pointer">
            <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }}>
            Active
          </label>
        </div>
        <div style="font-size:11px;color:var(--text3);margin-bottom:10px">
          Used: {{ $plan->used_count ?? 0 }} times &nbsp;|&nbsp; Key: {{ $plan->key }}
        </div>
        <button type="submit" class="btn-plan">Save Changes</button>
      </form>
    </div>
    @endforeach
  </div>

  <div style="margin-top:24px">
    <a href="{{ route('admin.index') }}" style="color:var(--purple);font-size:13px">&larr; Back to Dashboard</a>
  </div>
</div>
@endsection
