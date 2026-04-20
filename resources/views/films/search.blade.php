@extends('layouts.app')
@section('title', $keyword ? "Tìm: $keyword" : 'Tìm kiếm phim')

@section('content')
<div class="container">
  <form action="{{ route('films.search') }}" method="GET" class="search-hero">
    <input type="text" name="q" value="{{ $keyword }}" placeholder="Tìm tên phim, thể loại..." autofocus>
    <select name="platform">
      <option value="all">Tất cả nguồn</option>
      @foreach($platforms as $key => $name)
        <option value="{{ $key }}" {{ request('platform') === $key ? 'selected' : '' }}>{{ $name }}</option>
      @endforeach
    </select>
    <button type="submit">🔍 Tìm</button>
  </form>

  @if($keyword)
    <p class="search-meta">
      @if(count($results) > 0)
        Tìm thấy <strong>{{ count($results) }}</strong> kết quả cho "<strong>{{ $keyword }}</strong>"
      @else
        Không tìm thấy kết quả nào cho "<strong>{{ $keyword }}</strong>"
      @endif
    </p>
  @endif

  @if(!empty($results))
    <div class="film-grid">
      @foreach($results as $film)
        <a href="{{ route('films.detail', $film['id'] ?? $film['drama_id'] ?? '#') }}?platform={{ request('platform', 'all') }}"
           class="film-card">
          <div class="card-thumb">
            <img src="{{ $film['cover'] ?? $film['cover_url'] ?? asset('img/no-cover.png') }}"
                 alt="{{ $film['title'] ?? '' }}" loading="lazy">
            <div class="card-overlay"><span class="play-btn">▶</span></div>
            <span class="free-badge">3 tập free</span>
          </div>
          <div class="card-info">
            <p class="card-title">{{ Str::limit($film['title'] ?? 'Phim ngắn', 50) }}</p>
            @if(isset($film['platform']))<p class="card-genre">{{ strtoupper($film['platform']) }}</p>@endif
          </div>
        </a>
      @endforeach
    </div>
  @elseif($keyword)
    <div class="empty-state">
      <p style="font-size:48px;margin-bottom:12px">🔍</p>
      <p>Thử tìm với từ khóa khác hoặc chọn nguồn khác</p>
    </div>
  @else
    <div class="genre-list">
      <h2 style="margin-bottom:16px">Khám phá theo thể loại</h2>
      <div class="genre-grid">
        @foreach(['romance'=>'💕 Tình cảm','action'=>'⚔️ Hành động','comedy'=>'😄 Hài hước','thriller'=>'😱 Kinh dị','fantasy'=>'🧙 Giả tưởng','family'=>'👨‍👩‍👧 Gia đình'] as $slug => $label)
          <a href="{{ route('films.genre', $slug) }}" class="genre-btn">{{ $label }}</a>
        @endforeach
      </div>
    </div>
  @endif
</div>

@push('styles')
<style>
.search-hero { display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap }
.search-hero input { flex:1;min-width:200px;background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:12px 16px;border-radius:10px;font-size:14px;outline:none }
.search-hero input:focus { border-color:var(--brand) }
.search-hero select { background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:12px 14px;border-radius:10px;font-size:13px;cursor:pointer }
.search-hero button { background:var(--brand);border:none;color:white;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer }
.search-meta { font-size:14px;color:var(--text2);margin-bottom:16px }
.empty-state { text-align:center;padding:60px 20px;color:var(--text2) }
.genre-grid { display:flex;flex-wrap:wrap;gap:10px }
.genre-btn { padding:12px 20px;background:var(--bg2);border:1px solid var(--border);border-radius:10px;font-size:14px;transition:all .2s }
.genre-btn:hover { border-color:var(--brand);color:var(--brand) }
</style>
@endpush
@endsection
