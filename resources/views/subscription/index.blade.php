@extends('layouts.app')
@section('title', 'Mua gói VIP - Xem phim không giới hạn')

@section('content')
<div class="container sub-page">

  {{-- Header --}}
  <div class="sub-hero">
    <h1>🌟 Gói VIP {{ $vipDays }} ngày</h1>
    <p>Mở khóa toàn bộ nội dung từ 22+ nền tảng phim ngắn hàng đầu thế giới</p>
  </div>

  {{-- VIP Status --}}
  @if($user->isVip())
    <div class="alert alert-success" style="text-align:center;margin-bottom:24px">
      ✅ Bạn đang là thành viên VIP — hết hạn: <strong>{{ $user->vip_expires_at->format('d/m/Y') }}</strong>
    </div>
  @endif

  {{-- Features --}}
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">🎬</div>
      <h3>22+ Nền tảng</h3>
      <p>DramaBox, ReelShort, NetShort, GoodShort, ShortMax và nhiều hơn nữa</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">♾️</div>
      <h3>Xem không giới hạn</h3>
      <p>Toàn bộ tập phim, không giới hạn thời gian, không quảng cáo</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📱</div>
      <h3>Mọi thiết bị</h3>
      <p>Xem trên điện thoại, máy tính bảng, laptop — mọi lúc mọi nơi</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🌏</div>
      <h3>13 Ngôn ngữ</h3>
      <p>Tiếng Việt, Anh, Hàn, Nhật, Trung và nhiều ngôn ngữ khác</p>
    </div>
  </div>

  {{-- Pricing --}}
  <div class="pricing-box">
    <div class="pricing-main">
      <div class="price-vn">
        <span class="price-amount">99.000đ</span>
        <span class="price-period">/ {{ $vipDays }} ngày</span>
      </div>
      <div class="price-divider">hoặc</div>
      <div class="price-intl">
        <span class="price-amount">${{ $priceUsd }}</span>
        <span class="price-period">/ {{ $vipDays }} days</span>
      </div>
    </div>

    {{-- Payment buttons --}}
    @auth
      <div class="payment-methods">
        <p class="payment-label">Chọn phương thức thanh toán:</p>

        {{-- VNPay --}}
        <form action="{{ route('payment.vnpay') }}" method="POST">
          @csrf
          <button type="submit" class="btn-payment vnpay">
            <img src="{{ asset('img/vnpay-logo.png') }}" alt="VNPay" style="height:24px">
            Thanh toán VNPay <span class="currency-note">(99.000đ)</span>
          </button>
        </form>

        {{-- Stripe --}}
        <form action="{{ route('payment.stripe') }}" method="POST">
          @csrf
          <button type="submit" class="btn-payment stripe">
            💳 Thanh toán thẻ quốc tế (Stripe) <span class="currency-note">(${{ $priceUsd }})</span>
          </button>
        </form>
      </div>

      <p class="payment-note">
        🔒 Thanh toán bảo mật 100% — VNPay & Stripe được mã hóa SSL<br>
        ⚡ Kích hoạt ngay sau khi thanh toán thành công
      </p>
    @else
      <div style="text-align:center;padding:24px">
        <p style="margin-bottom:16px">Bạn cần đăng nhập để mua gói VIP</p>
        <a href="{{ route('login') }}" class="btn-vip-large">Đăng nhập ngay</a>
        <span style="margin:0 12px;color:#666">hoặc</span>
        <a href="{{ route('register') }}" class="btn-outline">Đăng ký miễn phí</a>
      </div>
    @endauth
  </div>

  {{-- Compare Free vs VIP --}}
  <div class="compare-table">
    <h2>So sánh gói</h2>
    <table>
      <thead>
        <tr><th>Tính năng</th><th>Miễn phí</th><th>VIP</th></tr>
      </thead>
      <tbody>
        <tr><td>Xem phim</td><td>3 tập đầu</td><td>✅ Không giới hạn</td></tr>
        <tr><td>Số nền tảng</td><td>Tất cả</td><td>✅ Tất cả 22+</td></tr>
        <tr><td>Chất lượng video</td><td>SD</td><td>✅ HD</td></tr>
        <tr><td>Quảng cáo</td><td>Có</td><td>✅ Không có</td></tr>
        <tr><td>Lưu lịch sử xem</td><td>✅</td><td>✅</td></tr>
        <tr><td>Tìm kiếm nâng cao</td><td>Cơ bản</td><td>✅ Đầy đủ</td></tr>
        <tr><td>Hỗ trợ</td><td>Cộng đồng</td><td>✅ Ưu tiên</td></tr>
      </tbody>
    </table>
  </div>

  {{-- Transaction History --}}
  @if($transactions->isNotEmpty())
    <div class="transaction-history">
      <h2>Lịch sử giao dịch</h2>
      <table class="tx-table">
        <thead>
          <tr><th>Ngày</th><th>Phương thức</th><th>Số tiền</th><th>Trạng thái</th><th>Hết hạn</th></tr>
        </thead>
        <tbody>
          @foreach($transactions as $tx)
            <tr>
              <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ strtoupper($tx->payment_method) }}</td>
              <td>
                @if($tx->currency === 'VND')
                  {{ number_format($tx->amount) }}đ
                @else
                  ${{ $tx->amount }}
                @endif
              </td>
              <td>
                <span class="status-badge status-{{ $tx->status }}">
                  {{ ['pending'=>'Chờ xử lý','paid'=>'Thành công','failed'=>'Thất bại'][$tx->status] ?? $tx->status }}
                </span>
              </td>
              <td>{{ $tx->expires_at?->format('d/m/Y') ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

</div>
@endsection
