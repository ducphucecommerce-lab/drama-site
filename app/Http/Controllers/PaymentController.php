<?php
namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\VNPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller
{
    public function __construct(private VNPayService $vnpay) {}

    // ── Trang mua gói ────────────────────────────────────────
    public function index()
    {
        $user        = auth()->user();
        $vipDays     = (int) config('services.vip.duration_days', 30);
        $priceVnd    = (int) config('services.vip.price_vnd', 99000);
        $priceUsd    = (float) config('services.vip.price_usd', 5);
        $transactions= $user->subscriptions()->latest()->take(5)->get();

        return view('subscription.index', compact('user', 'vipDays', 'priceVnd', 'priceUsd', 'transactions'));
    }

    // ── Thanh toán VNPay ─────────────────────────────────────
    public function vnpayCheckout(Request $request)
    {
        $user      = auth()->user();
        $orderCode = 'VIP' . strtoupper(Str::random(8));
        $amount    = (int) config('services.vip.price_vnd', 99000);

        // Tạo bản ghi pending
        Subscription::create([
            'user_id'        => $user->id,
            'plan'           => 'vip',
            'payment_method' => 'vnpay',
            'transaction_id' => $orderCode,
            'amount'         => $amount,
            'currency'       => 'VND',
            'status'         => 'pending',
        ]);

        $payUrl = $this->vnpay->createPaymentUrl(
            $amount,
            $orderCode,
            'Mua gói VIP 30 ngày - ' . $user->email
        );

        return redirect($payUrl);
    }

    // ── Callback VNPay ───────────────────────────────────────
    public function vnpayReturn(Request $request)
    {
        if (!$this->vnpay->verifyReturn($request)) {
            return redirect()->route('subscription.index')
                ->with('error', 'Thanh toán thất bại hoặc bị giả mạo.');
        }

        $orderCode   = $request->get('vnp_TxnRef');
        $subscription= Subscription::where('transaction_id', $orderCode)->firstOrFail();

        if ($subscription->status === 'paid') {
            return redirect()->route('home')->with('success', 'Gói VIP đã được kích hoạt!');
        }

        $subscription->update([
            'status'     => 'paid',
            'starts_at'  => now(),
            'expires_at' => now()->addDays(30),
            'metadata'   => $request->all(),
        ]);

        $subscription->user->activateVip(30);

        return redirect()->route('home')
            ->with('success', '🎉 Kích hoạt VIP thành công! Chúc bạn xem phim vui vẻ.');
    }

    // ── Thanh toán Stripe ────────────────────────────────────
    public function stripeCheckout(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $user      = auth()->user();
        $orderCode = 'VIP' . strtoupper(Str::random(8));
        $priceUsd  = (float) config('services.vip.price_usd', 5);

        Subscription::create([
            'user_id'        => $user->id,
            'plan'           => 'vip',
            'payment_method' => 'stripe',
            'transaction_id' => $orderCode,
            'amount'         => $priceUsd,
            'currency'       => 'USD',
            'status'         => 'pending',
        ]);

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => (int)($priceUsd * 100),
                    'product_data' => ['name' => 'Gói VIP 30 ngày'],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('payment.stripe.success') . '?order=' . $orderCode,
            'cancel_url'  => route('subscription.index'),
            'metadata'    => ['order_code' => $orderCode, 'user_id' => $user->id],
        ]);

        return redirect($session->url);
    }

    // ── Stripe Success ───────────────────────────────────────
    public function stripeSuccess(Request $request)
    {
        $orderCode   = $request->get('order');
        $subscription= Subscription::where('transaction_id', $orderCode)
            ->where('status', 'pending')
            ->firstOrFail();

        $subscription->update([
            'status'     => 'paid',
            'starts_at'  => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $subscription->user->activateVip(30);

        return redirect()->route('home')
            ->with('success', '🎉 Kích hoạt VIP thành công! Chúc bạn xem phim vui vẻ.');
    }

    // ── Stripe Webhook (xác nhận server-side) ────────────────
    public function stripeWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );

            if ($event->type === 'checkout.session.completed') {
                $session   = $event->data->object;
                $orderCode = $session->metadata->order_code ?? null;

                if ($orderCode) {
                    $sub = Subscription::where('transaction_id', $orderCode)->first();
                    if ($sub && $sub->status === 'pending') {
                        $sub->update(['status' => 'paid', 'starts_at' => now(), 'expires_at' => now()->addDays(30)]);
                        $sub->user->activateVip(30);
                    }
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
