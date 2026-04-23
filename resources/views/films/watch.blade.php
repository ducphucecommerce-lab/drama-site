@extends('layouts.app')
@section('title', ($film['title'] ?? 'Watch') . ' - Ep ' . $episode . ' - ' . config('app.name'))

@section('content')
<div class="watch-wrap">

  <div class="breadcrumb">
    <a href="{{ route('home') }}?platform={{ $platform }}">{{ app()->getLocale() === 'vi' ? 'Trang chu' : 'Home' }}</a>
    <span>/</span>
    <a href="{{ route('films.detail', $id) }}?platform={{ $platform }}">{{ Str::limit($film['title'] ?? '', 35) }}</a>
    <span>/</span>
    <span>{{ app()->getLocale() === 'vi' ? 'Tap' : 'Ep' }} {{ $episode }}</span>
  </div>

  <div class="watch-container">

    {{-- VIDEO --}}
    <div class="watch-player-wrap">
      @if($streamUrl)
        <video id="player" src="{{ $streamUrl }}" controls autoplay playsinline
          poster="{{ $film['cover'] ?? '' }}" class="watch-video"></video>
      @else
        <div class="watch-error">
          <div class="watch-error-icon">:(</div>
          <p class="watch-error-title">{{ app()->getLocale() === 'vi' ? 'Khong the tai video' : 'Video unavailable' }}</p>
          <p class="watch-error-sub">{{ app()->getLocale() === 'vi' ? 'Vui long thu tap khac' : 'Please try another episode' }}</p>
        </div>
      @endif
    </div>

    {{-- INFO BAR --}}
    <div class="watch-info-bar">
      <div class="watch-title-wrap">
        <h1 class="watch-film-title">{{ $film['title'] ?? 'Short Drama' }}</h1>
        <span class="watch-ep-badge">{{ app()->getLocale() === 'vi' ? 'Tap' : 'Ep' }} {{ $episode }}</span>
      </div>
      <div class="watch-nav-btns">
        @if($episode > 1)
          <a href="{{ route('films.watch', $id) }}?platform={{ $platform }}&ep={{ $episode - 1 }}"
             class="watch-nav-btn">
            &larr; {{ app()->getLocale() === 'vi' ? 'Tap truoc' : 'Previous' }}
          </a>
        @endif
        @php
          $totalEps = $film['episodes'] ?? $film['total_episodes'] ?? 0;
          $nextEp   = $episode + 1;
          $canNext  = $nextEp <= 3 || (auth()->check() && auth()->user()->isVip());
        @endphp
        @if($totalEps && $episode < $totalEps)
          @if($canNext)
            <a href="{{ route('films.watch', $id) }}?platform={{ $platform }}&ep={{ $nextEp }}"
               class="watch-nav-btn watch-nav-next btn-ep-next">
              {{ app()->getLocale() === 'vi' ? 'Tap tiep' : 'Next' }} &rarr;
            </a>
          @else
            <a href="{{ route('subscription.index') }}" class="watch-nav-btn watch-nav-vip">
              [VIP] {{ app()->getLocale() === 'vi' ? 'de xem tap' : 'to watch ep' }} {{ $nextEp }}
            </a>
          @endif
        @endif
      </div>
      @if($episode <= 3)
        <div class="watch-free-notice">
          {{ app()->getLocale() === 'vi' ? 'Tap mien phi' : 'Free episode' }} &mdash;
          <a href="{{ route('subscription.index') }}">{{ app()->getLocale() === 'vi' ? 'Mua VIP xem full' : 'Get VIP for unlimited access' }}</a>
        </div>
      @endif
    </div>

    {{-- EPISODE GRID --}}
    <div class="watch-ep-section">
      <div class="watch-ep-header">
        <span class="watch-ep-title">{{ app()->getLocale() === 'vi' ? 'Danh sach tap' : 'Episodes' }}</span>
        <span class="watch-ep-count">{{ $film['episodes'] ?? $film['total_episodes'] ?? 0 }} {{ app()->getLocale() === 'vi' ? 'tap' : 'eps' }}</span>
      </div>
      <div class="watch-ep-grid">
        @php
          $epList = $film['episode_list'] ?? [];
          $total  = $film['episodes'] ?? $film['total_episodes'] ?? 20;
          $isVip  = auth()->check() && auth()->user()->isVip();
        @endphp
        @if(!empty($epList))
          @foreach($epList as $ep)
            @php
              $epNum    = $ep['episode'] ?? $ep['index'] ?? $loop->iteration;
              $canWatch = $epNum <= 3 || $isVip;
              $active   = $epNum == $episode;
            @endphp
            <a href="{{ $canWatch ? route('films.watch', $id).'?platform='.$platform.'&ep='.$epNum : route('subscription.index') }}"
               class="watch-ep-item {{ $active ? 'active' : '' }} {{ !$canWatch ? 'locked' : '' }}">
              {{ $epNum }}
            </a>
          @endforeach
        @else
          @for($i = 1; $i <= $total; $i++)
            @php $canWatch = $i <= 3 || $isVip; $active = $i == $episode; @endphp
            <a href="{{ $canWatch ? route('films.watch', $id).'?platform='.$platform.'&ep='.$i : route('subscription.index') }}"
               class="watch-ep-item {{ $active ? 'active' : '' }} {{ !$canWatch ? 'locked' : '' }}">
              {{ $i }}
            </a>
          @endfor
        @endif
      </div>
    </div>

    {{-- COMMENT SECTION --}}
    <div class="comment-section">
      <div class="comment-header">
        <span class="comment-title">{{ app()->getLocale() === 'vi' ? 'Binh luan' : 'Comments' }}</span>
        <span class="comment-ep-label">{{ app()->getLocale() === 'vi' ? 'Tap' : 'Ep' }} {{ $episode }}</span>
      </div>

      @auth
      <div class="comment-form">
        <div class="comment-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div class="comment-input-wrap">
          <textarea id="commentInput"
            placeholder="{{ app()->getLocale() === 'vi' ? 'Viet binh luan...' : 'Write a comment...' }}"
            maxlength="500" rows="2"></textarea>
          <div class="comment-form-footer">
            <span id="commentCount" class="comment-char-count">0/500</span>
            <button id="commentSubmit" class="comment-submit-btn">
              {{ app()->getLocale() === 'vi' ? 'Gui' : 'Post' }}
            </button>
          </div>
        </div>
      </div>
      @else
      <div class="comment-login-notice">
        <a href="{{ route('login') }}">{{ app()->getLocale() === 'vi' ? 'Dang nhap' : 'Sign in' }}</a>
        {{ app()->getLocale() === 'vi' ? 'de binh luan' : 'to comment' }}
      </div>
      @endauth

      <div id="commentList" class="comment-list">
        <div class="comment-loading">{{ app()->getLocale() === 'vi' ? 'Dang tai...' : 'Loading...' }}</div>
      </div>
    </div>

  </div>{{-- end watch-container --}}
</div>{{-- end watch-wrap --}}

@push('styles')
<style>
.watch-wrap{padding:70px 0 100px;background:#070710;min-height:100vh}
.breadcrumb{padding:12px 20px;display:flex;gap:8px;align-items:center;font-size:12px;color:var(--text3);background:rgba(255,255,255,0.02);flex-wrap:wrap}
.breadcrumb a{color:var(--text3);transition:color .2s}.breadcrumb a:hover{color:#fff}
.watch-container{max-width:860px;margin:0 auto;padding:0 16px 40px}
.watch-player-wrap{background:#000;overflow:hidden;aspect-ratio:9/16;max-height:80vh;width:100%}
.watch-video{width:100%;height:100%;object-fit:contain;display:block}
.watch-error{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#0d0d1a;min-height:300px;padding:20px;text-align:center}
.watch-error-icon{font-size:48px;margin-bottom:16px;color:var(--text3)}
.watch-error-title{font-size:15px;font-weight:600;color:#fff;margin-bottom:8px}
.watch-error-sub{font-size:13px;color:var(--text3)}
.watch-info-bar{padding:16px 0;border-bottom:1px solid var(--border)}
.watch-title-wrap{display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap}
.watch-film-title{font-size:16px;font-weight:600;color:#fff;line-height:1.3;flex:1}
.watch-ep-badge{background:rgba(167,139,250,0.15);border:1px solid rgba(167,139,250,0.3);color:var(--purple);font-size:11px;padding:3px 10px;border-radius:20px;white-space:nowrap;flex-shrink:0}
.watch-nav-btns{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
.watch-nav-btn{padding:9px 18px;border-radius:var(--radius);font-size:13px;border:1px solid var(--border);color:var(--text2);transition:all .2s;white-space:nowrap;text-decoration:none;display:inline-block}
.watch-nav-btn:hover{border-color:var(--border2);color:#fff}
.watch-nav-next{background:var(--grad);border-color:transparent;color:#fff!important;font-weight:600}
.watch-nav-next:hover{opacity:0.88}
.watch-nav-vip{background:rgba(245,158,11,0.1);border-color:rgba(245,158,11,0.3);color:#fbbf24}
.watch-free-notice{font-size:12px;color:var(--text3)}.watch-free-notice a{color:var(--purple)}
.watch-ep-section{padding:16px 0;border-bottom:1px solid var(--border)}
.watch-ep-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.watch-ep-title{font-size:14px;font-weight:600;color:#fff}
.watch-ep-count{font-size:12px;color:var(--text3)}
.watch-ep-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(50px,1fr));gap:8px}
.watch-ep-item{aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:var(--radius);font-size:13px;font-weight:500;border:1px solid var(--border);color:var(--text2);cursor:pointer;transition:all .15s;text-decoration:none}
.watch-ep-item:hover{border-color:var(--purple);color:var(--purple);background:rgba(167,139,250,0.08)}
.watch-ep-item.active{background:rgba(167,139,250,0.15);border-color:var(--purple);color:var(--purple);font-weight:700}
.watch-ep-item.locked{opacity:0.5}
/* Comments */
.comment-section{padding:20px 0}
.comment-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.comment-title{font-size:15px;font-weight:600;color:#fff}
.comment-ep-label{font-size:12px;color:var(--text3);background:rgba(255,255,255,0.06);padding:3px 10px;border-radius:20px}
.comment-form{display:flex;gap:10px;margin-bottom:20px}
.comment-avatar{width:36px;height:36px;border-radius:50%;background:var(--grad);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.comment-input-wrap{flex:1}
.comment-input-wrap textarea{width:100%;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);padding:10px 12px;font-size:13px;color:#fff;resize:none;outline:none;transition:border-color .2s;font-family:inherit}
.comment-input-wrap textarea:focus{border-color:rgba(167,139,250,0.4)}
.comment-input-wrap textarea::placeholder{color:var(--text3)}
.comment-form-footer{display:flex;justify-content:space-between;align-items:center;margin-top:8px}
.comment-char-count{font-size:11px;color:var(--text3)}
.comment-submit-btn{background:var(--grad);border:none;border-radius:var(--radius);padding:7px 18px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;transition:opacity .2s}
.comment-submit-btn:hover{opacity:0.88}
.comment-login-notice{text-align:center;padding:20px;font-size:14px;color:var(--text3);background:rgba(255,255,255,0.02);border-radius:var(--radius);margin-bottom:16px}
.comment-login-notice a{color:var(--purple);font-weight:600}
.comment-list{display:flex;flex-direction:column;gap:12px}
.comment-loading{text-align:center;color:var(--text3);font-size:13px;padding:24px}
.comment-item{display:flex;gap:10px;animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.comment-item-avatar{width:34px;height:34px;border-radius:50%;background:var(--bg3);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--purple);flex-shrink:0}
.comment-item-body{flex:1;background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:var(--radius);padding:12px}
.comment-item-header{display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap}
.comment-item-name{font-size:13px;font-weight:600;color:#fff}
.comment-vip-tag{background:var(--grad);font-size:9px;font-weight:700;padding:2px 7px;border-radius:10px;color:#fff}
.comment-item-time{font-size:11px;color:var(--text3);margin-left:auto}
.comment-item-content{font-size:13px;color:rgba(255,255,255,0.8);line-height:1.6;word-break:break-word}
.comment-item-actions{display:flex;gap:12px;margin-top:8px}
.comment-like-btn,.comment-delete-btn{background:none;border:none;font-size:12px;color:var(--text3);cursor:pointer;display:flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;transition:all .2s}
.comment-like-btn:hover{color:var(--purple);background:rgba(167,139,250,0.1)}
.comment-delete-btn:hover{color:#f87171;background:rgba(239,68,68,0.1)}
.comment-empty{text-align:center;padding:32px;color:var(--text3);font-size:13px;background:rgba(255,255,255,0.02);border-radius:var(--radius)}
@media(min-width:769px){
  .watch-wrap{padding:70px 0 40px}
  .watch-player-wrap{border-radius:var(--radius-lg);max-height:75vh}
  .watch-film-title{font-size:18px}
  .watch-ep-grid{grid-template-columns:repeat(auto-fill,minmax(56px,1fr))}
}
</style>
@endpush

@push('scripts')
<script>
(function(){
  var player = document.getElementById('player');
  if (player) {
    var key = 'wp_{{ $id }}_{{ $episode }}';
    var saved = parseFloat(localStorage.getItem(key));
    if (saved > 5) player.addEventListener('loadedmetadata', function(){ player.currentTime = saved; }, {once:true});
    player.addEventListener('timeupdate', function(){
      if (Math.floor(player.currentTime) % 5 === 0) localStorage.setItem(key, player.currentTime);
    });
    player.addEventListener('ended', function(){
      var next = document.querySelector('.watch-nav-next');
      if (next) setTimeout(function(){ window.location = next.href; }, 1500);
    });
  }
  var activeEp = document.querySelector('.watch-ep-item.active');
  if (activeEp) activeEp.scrollIntoView({block:'nearest', behavior:'smooth'});

  // Comments
  var DRAMA_ID = '{{ $id }}';
  var PLATFORM = '{{ $platform }}';
  var EPISODE  = {{ $episode }};
  var CSRF     = (document.querySelector('meta[name="csrf-token"]') || {}).content;
  var isVi     = {{ app()->getLocale() === 'vi' ? 'true' : 'false' }};

  function loadComments() {
    var list = document.getElementById('commentList');
    if (!list) return;
    fetch('/comments?drama_id=' + DRAMA_ID + '&platform=' + PLATFORM + '&episode=' + EPISODE)
      .then(function(r){ return r.json(); })
      .then(function(data){
        if (!data.length) {
          list.innerHTML = '<div class="comment-empty">' + (isVi ? 'Chua co binh luan nao. Hay la nguoi dau tien!' : 'No comments yet. Be the first!') + '</div>';
          return;
        }
        list.innerHTML = data.map(function(c){
          return '<div class="comment-item" id="comment-' + c.id + '">' +
            '<div class="comment-item-avatar">' + c.user.charAt(0).toUpperCase() + '</div>' +
            '<div class="comment-item-body">' +
              '<div class="comment-item-header">' +
                '<span class="comment-item-name">' + c.user + '</span>' +
                (c.is_vip ? '<span class="comment-vip-tag">VIP</span>' : '') +
                '<span class="comment-item-time">' + c.time + '</span>' +
              '</div>' +
              '<div class="comment-item-content">' + c.content + '</div>' +
              '<div class="comment-item-actions">' +
                '<button class="comment-like-btn" onclick="likeComment(' + c.id + ',this)">Like <span>' + c.likes + '</span></button>' +
                (c.can_delete ? '<button class="comment-delete-btn" onclick="deleteComment(' + c.id + ')">' + (isVi?'Xoa':'Delete') + '</button>' : '') +
              '</div>' +
            '</div>' +
          '</div>';
        }).join('');
      }).catch(function(e){ console.error(e); });
  }

  window.likeComment = function(id, btn) {
    fetch('/comments/' + id + '/like', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}})
      .then(function(r){ return r.json(); })
      .then(function(d){ btn.querySelector('span').textContent = d.likes; });
  };

  window.deleteComment = function(id) {
    if (!confirm(isVi ? 'Xoa binh luan nay?' : 'Delete this comment?')) return;
    fetch('/comments/' + id, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF}})
      .then(function(){ document.getElementById('comment-' + id) && document.getElementById('comment-' + id).remove(); });
  };

  var input   = document.getElementById('commentInput');
  var countEl = document.getElementById('commentCount');
  if (input) {
    input.addEventListener('input', function(){ countEl.textContent = input.value.length + '/500'; });
  }

  var submitBtn = document.getElementById('commentSubmit');
  if (submitBtn) {
    submitBtn.addEventListener('click', function(){
      var content = input && input.value.trim();
      if (!content) return;
      submitBtn.disabled = true;
      submitBtn.textContent = '...';
      fetch('/comments', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({drama_id:DRAMA_ID, platform:PLATFORM, episode:EPISODE, content:content})
      }).then(function(r){
        if (r.ok) { input.value = ''; countEl.textContent = '0/500'; loadComments(); }
        submitBtn.disabled = false;
        submitBtn.textContent = isVi ? 'Gui' : 'Post';
      });
    });
  }

  loadComments();
})();
</script>
@endpush
@endsection