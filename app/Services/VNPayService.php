<?php
namespace App\Services;

use Illuminate\Http\Request;

class VNPayService
{
    private string $tmnCode;
    private string $hashSecret;
    private string $url;
    private string $returnUrl;

    public function __construct()
    {
        $this->tmnCode   = config('services.vnpay.tmn_code');
        $this->hashSecret= config('services.vnpay.hash_secret');
        $this->url       = config('services.vnpay.url');
        $this->returnUrl = config('services.vnpay.return_url');
    }

    // Tạo URL thanh toán VNPay
    public function createPaymentUrl(int $amount, string $orderCode, string $orderInfo): string
    {
        $params = [
            'vnp_Version'    => '2.1.0',
            'vnp_Command'    => 'pay',
            'vnp_TmnCode'    => $this->tmnCode,
            'vnp_Amount'     => $amount * 100, // VNPay tính theo đơn vị nhỏ nhất
            'vnp_CurrCode'   => 'VND',
            'vnp_TxnRef'     => $orderCode,
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => 'other',
            'vnp_Locale'     => 'vn',
            'vnp_ReturnUrl'  => $this->returnUrl,
            'vnp_IpAddr'     => request()->ip(),
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_ExpireDate' => now()->addMinutes(15)->format('YmdHis'),
        ];

        ksort($params);

        $query     = http_build_query($params);
        $signature = hash_hmac('sha512', $query, $this->hashSecret);

        return $this->url . '?' . $query . '&vnp_SecureHash=' . $signature;
    }

    // Xác minh callback từ VNPay
    public function verifyReturn(Request $request): bool
    {
        $vnpSecureHash = $request->get('vnp_SecureHash');
        $inputData     = $request->except(['vnp_SecureHash', 'vnp_SecureHashType']);

        ksort($inputData);
        $query    = http_build_query($inputData);
        $myHash   = hash_hmac('sha512', $query, $this->hashSecret);

        return hash_equals($myHash, $vnpSecureHash)
            && $request->get('vnp_ResponseCode') === '00';
    }
}
