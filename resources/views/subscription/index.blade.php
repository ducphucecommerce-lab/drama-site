@extends('layouts.app')
@section('title', app()->getLocale() === 'vi' ? 'Nang cap VIP - ' . config('app.name') : 'Upgrade to VIP - ' . config('app.name'))

@section('content')
<div class="sub-wrap">

  <div class="sub-header">
    <h1 class="sub-title">
      {{ app()->getLocale() === 'vi' ? 'Nang cap' : 'Upgrade to' }}
      <span>VIP</span>
    </h1>
    <p class="sub-desc">
      {{ app()->getLocale() === 'vi' ? 'Xem khong gioi han tu 22+ nen tang' : 'Watch unlimited from 22+ platforms' }}
    </p>
  </div>

  {{-- VIP Benefits --}}
  <div class="sub-benefits">
    <div class="sub-benefit-item">
      <div class="sub-benefit-icon">&#10003;</div>
      <span>{{ app()->getLocale() === 'vi' ? 'Xem tat ca cac tap khong gioi han' : 'Watch all episodes unlimited' }}</span>
    </div>
    <div class="sub-benefit-item">
      <div class="sub-benefit-icon">&#10003;</div>
      <span>{{ app()->getLocale() === 'vi' ? 'Khong quang cao' : 'No ads' }}</span>
    </div>
    <div class="sub-benefit-item">
      <div class="sub-benefit-icon">&#10003;</div>
      <span>{{ app()->getLocale() === 'vi' ? 'Truy cap 22+ nen tang phim' : 'Access 22+ drama platforms' }}</span>
    </div>
    <div class="sub-benefit-item">
      <div class="sub-benefit-icon">&#10003;</div>
      <span>{{ app()->getLocale() === 'vi' ? 'Ho tro uu tien' : 'Priority support' }}</span>
    </div>
  </div>

  {{-- Plans --}}
  <div class="plan-grid">

    {{-- 1 Month --}}
    <div class="plan-card">
      <div class="plan-name">{{ app()->getLocale() === 'vi' ? '1 Thang' : '1 Month' }}</div>
      <div class="plan-price">$5<span>/{{ app()->getLocale() === 'vi' ? 'thang' : 'mo' }}</span></div>
      <div class="plan-save"></div>
      <ul class="plan-features">
        <li>{{ app()->getLocale() === 'vi' ? 'Xem khong gioi han 30 ngay' : 'Unlimited for 30 days' }}</li>
        <li>{{ app()->getLocale() === 'vi' ? 'Gia thap nhat de thu' : 'Best price to try' }}</li>
      </ul>
      @auth
        <form action="{{ route('payment.checkout') }}" method="POST">
          @csrf
          <input type="hidden" name="plan" value="1month">
          <input type="hidden" name="coupon_code" id="coupon_1month">
          <button type="submit" class="btn-plan btn-plan-outline">
            {{ app()->getLocale() === 'vi' ? 'Chon goi nay' : 'Choose Plan' }}
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn-plan btn-plan-outline">
          {{ app()->getLocale() === 'vi' ? 'Dang nhap de mua' : 'Sign in to buy' }}
        </a>
      @endauth
    </div>

    {{-- 3 Months (Featured) --}}
    <div class="plan-card featured">
      <div class="plan-badge">{{ app()->getLocale() === 'vi' ? 'Pho bien nhat' : 'Most Popular' }}</div>
      <div class="plan-name">{{ app()->getLocale() === 'vi' ? '3 Thang' : '3 Months' }}</div>
      <div class="plan-price">$12<span>/3 {{ app()->getLocale() === 'vi' ? 'thang' : 'mo' }}</span></div>
      <div class="plan-save">{{ app()->getLocale() === 'vi' ? 'Tiet kiem 20%' : 'Save 20%' }}</div>
      <ul class="plan-features">
        <li>{{ app()->getLocale() === 'vi' ? 'Xem khong gioi han 90 ngay' : 'Unlimited for 90 days' }}</li>
        <li>{{ app()->getLocale() === 'vi' ? 'Tiet kiem $3 so voi goi 1 thang' : 'Save $3 vs monthly' }}</li>
      </ul>
      @auth
        <form action="{{ route('payment.checkout') }}" method="POST">
          @csrf
          <input type="hidden" name="plan" value="3months">
          <input type="hidden" name="coupon_code" id="coupon_3months">
          <button type="submit" class="btn-plan">
            {{ app()->getLocale() === 'vi' ? 'Chon goi nay' : 'Choose Plan' }}
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn-plan">
          {{ app()->getLocale() === 'vi' ? 'Dang nhap de mua' : 'Sign in to buy' }}
        </a>
      @endauth
    </div>

    {{-- 6 Months --}}
    <div class="plan-card">
      <div class="plan-name">{{ app()->getLocale() === 'vi' ? '6 Thang' : '6 Months' }}</div>
      <div class="plan-price">$20<span>/6 {{ app()->getLocale() === 'vi' ? 'thang' : 'mo' }}</span></div>
      <div class="plan-save">{{ app()->getLocale() === 'vi' ? 'Tiet kiem 33%' : 'Save 33%' }}</div>
      <ul class="plan-features">
        <li>{{ app()->getLocale() === 'vi' ? 'Xem khong gioi han 180 ngay' : 'Unlimited for 180 days' }}</li>
        <li>{{ app()->getLocale() === 'vi' ? 'Tiet kiem $10 so voi goi 1 thang' : 'Save $10 vs monthly' }}</li>
      </ul>
      @auth
        <form action="{{ route('payment.checkout') }}" method="POST">
          @csrf
          <input type="hidden" name="plan" value="6months">
          <input type="hidden" name="coupon_code" id="coupon_6months">
          <button type="submit" class="btn-plan btn-plan-outline">
            {{ app()->getLocale() === 'vi' ? 'Chon goi nay' : 'Choose Plan' }}
          </button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn-plan btn-plan-outline">
          {{ app()->getLocale() === 'vi' ? 'Dang nhap de mua' : 'Sign in to buy' }}
        </a>
      @endauth
    </div>

  </div>

  {{-- Coupon Code --}}
  <div class="coupon-section">
    <div class="coupon-title">{{ app()->getLocale() === 'vi' ? 'Ma khuyen mai' : 'Coupon Code' }}</div>
    <div class="coupon-form">
      <input type="text" id="couponInput" placeholder="{{ app()->getLocale() === 'vi' ? 'Nhap ma khuyen mai...' : 'Enter coupon code...' }}" class="coupon-input">
      <button onclick="applyCoupon()" class="coupon-btn">{{ app()->getLocale() === 'vi' ? 'Ap dung' : 'Apply' }}</button>
    </div>
    <div id="couponMsg" class="coupon-msg"></div>
  </div>

  @auth
    @if(auth()->user()->isVip())
    <div class="current-vip">
      <div class="current-vip-title">{{ app()->getLocale() === 'vi' ? 'Goi VIP hien tai cua ban' : 'Your current VIP' }}</div>
      <div class="current-vip-expire">
        {{ app()->getLocale() === 'vi' ? 'Het han:' : 'Expires:' }}
        {{ auth()->user()->vip_expires_at ? auth()->user()->vip_expires_at->format('d/m/Y') : 'N/A' }}
      </div>
    </div>
    @endif
  @endauth

</div>

@push('styles')
<style>
.sub-wrap{padding:30px 24px 100px;max-width:960px;margin:0 auto}
.sub-header{text-align:center;margin-bottom:24px}
.sub-title{font-size:28px;font-weight:700;color:#fff;margin-bottom:8px}
.sub-title span{background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.sub-desc{font-size:14px;color:var(--text2)}
.sub-benefits{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-bottom:28px}
.sub-benefit-item{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text2);background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:20px;padding:7px 14px}
.sub-benefit-icon{width:18px;height:18px;border-radius:50%;background:rgba(52,211,153,0.2);color:#34d399;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0}
.plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.plan-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-xl);padding:24px;transition:border-color .2s;position:relative}
.plan-card.featured{border-color:rgba(167,139,250,0.5);background:rgba(167,139,250,0.05)}
.plan-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--grad);font-size:11px;font-weight:700;padding:3px 14px;border-radius:20px;color:#fff;white-space:nowrap}
.plan-name{font-size:13px;color:var(--text2);margin-bottom:6px;font-weight:500}
.plan-price{font-size:32px;font-weight:700;color:#fff;margin-bottom:2px}
.plan-price span{font-size:13px;font-weight:400;color:var(--text2)}
.plan-save{font-size:12px;color:#34d399;margin-bottom:14px;min-height:18px}
.plan-features{list-style:none;margin-bottom:18px;display:flex;flex-direction:column;gap:8px}
.plan-features li{font-size:13px;color:var(--text2);display:flex;align-items:center;gap:7px;padding-left:4px}
.plan-features li::before{content:'?';color:#34d399;font-size:12px;flex-shrink:0}
.btn-plan{width:100%;background:var(--grad);border:none;border-radius:var(--radius);padding:11px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;transition:opacity .2s;display:block;text-align:center;text-decoration:none}
.btn-plan:hover{opacity:0.88}
.btn-plan-outline{background:transparent;border:1px solid var(--border2);color:var(--text2)}
.btn-plan-outline:hover{background:rgba(255,255,255,0.04);opacity:1;color:#fff}
.coupon-section{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;margin-bottom:20px}
.coupon-title{font-size:14px;font-weight:600;color:#fff;margin-bottom:12px}
.coupon-form{display:flex;gap:10px}
.coupon-input{flex:1;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:var(--radius);padding:10px 14px;font-size:13px;color:#fff;outline:none;transition:border-color .2s}
.coupon-input:focus{border-color:rgba(167,139,250,0.4)}
.coupon-input::placeholder{color:var(--text3)}
.coupon-btn{background:var(--grad);border:none;border-radius:var(--radius);padding:10px 20px;font-size:13px;font-weight:600;color:#fff;cursor:pointer;white-space:nowrap}
.coupon-msg{margin-top:10px;font-size:13px}
.coupon-msg.success{color:#34d399}
.coupon-msg.error{color:#f87171}
.current-vip{background:rgba(167,139,250,0.08);border:1px solid rgba(167,139,250,0.2);border-radius:var(--radius-lg);padding:16px 20px;text-align:center}
.current-vip-title{font-size:14px;font-weight:600;color:#fff;margin-bottom:4px}
.current-vip-expire{font-size:13px;color:var(--text2)}
@media(max-width:768px){
  .plan-grid{grid-template-columns:1fr;gap:20px}
  .plan-card.featured{margin-top:12px}
  .sub-wrap{padding:20px 16px 100px}
  .sub-title{font-size:22px}
}
</style>
@endpush

@push('scripts')
<script>
function applyCoupon() {
  var code = document.getElementById('couponInput').value.trim().toUpperCase();
  var msg  = document.getElementById('couponMsg');
  if (!code) return;

  fetch('/coupon/check?code=' + encodeURIComponent(code))
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.valid) {
        msg.className = 'coupon-msg success';
        msg.textContent = (data.discount_type === 'percent')
          ? 'Ma hop le! Giam ' + data.discount_value + '%'
          : 'Ma hop le! Giam $' + data.discount_value;
        document.getElementById('coupon_1month').value  = code;
        document.getElementById('coupon_3months').value = code;
        document.getElementById('coupon_6months').value = code;
      } else {
        msg.className = 'coupon-msg error';
        msg.textContent = data.message || 'Ma khong hop le';
      }
    }).catch(function(){
      msg.className = 'coupon-msg error';
      msg.textContent = 'Loi ket noi, thu lai sau';
    });
}
document.getElementById('couponInput').addEventListener('keypress', function(e){
  if (e.key === 'Enter') applyCoupon();
});
</script>
@endpush
@endsection