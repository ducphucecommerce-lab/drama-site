<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Coupon;

class PaymentController extends Controller
{
    // Gói VIP
    private array $plans = [
        '1month'  => ['days' => 30,  'price' => 5,  'label' => '1 Month'],
        '3months' => ['days' => 90,  'price' => 12, 'label' => '3 Months'],
        '6months' => ['days' => 180, 'price' => 20, 'label' => '6 Months'],
    ];

    public function index()
    {
        return view('subscription.index');
    }

    public function checkout(Request $request)
    {
        $plan = $request->input('plan', '1month');
        if (!isset($this->plans[$plan])) {
            return redirect()->route('subscription.index')->with('error', 'Invalid plan');
        }

        $planData    = $this->plans[$plan];
        $price       = $planData['price'];
        $couponCode  = strtoupper(trim($request->input('coupon_code', '')));
        $coupon      = null;
        $discount    = 0;

        // Apply coupon
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->where(function($q) {
                    $q->whereNull('max_uses')->orWhereRaw('used_count < max_uses');
                })
                ->first();

            if ($coupon) {
                if ($coupon->discount_type === 'percent') {
                    $discount = round($price * $coupon->discount_value / 100, 2);
                } else {
                    $discount = min($coupon->discount_value, $price);
                }
                $price = max(0, $price - $discount);
            }
        }

        // N?u giá = 0 (100% discount) ? kích ho?t VIP ngay
        if ($price <= 0 && $coupon) {
            $this->activateVip(auth()->user(), $planData['days']);
            if ($coupon) $coupon->increment('used_count');
            return redirect()->route('home')->with('success', 'VIP activated successfully!');
        }

        // Stripe checkout
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'usd',
                    'product_data' => ['name' => 'DramaSnap VIP - ' . $planData['label']],
                    'unit_amount'  => (int)($price * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('payment.success') . '?plan=' . $plan . '&coupon=' . $couponCode . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('subscription.index'),
            'metadata'    => [
                'user_id'     => auth()->id(),
                'plan'        => $plan,
                'coupon_code' => $couponCode,
            ],
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $plan       = $request->input('plan', '1month');
        $sessionId  = $request->input('session_id');
        $couponCode = strtoupper(trim($request->input('coupon', '')));

        if (!$sessionId || !isset($this->plans[$plan])) {
            return redirect()->route('home');
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $user = auth()->user();
                $this->activateVip($user, $this->plans[$plan]['days']);

                // Mark coupon used
                if ($couponCode) {
                    Coupon::where('code', $couponCode)->increment('used_count');
                }

                return redirect()->route('home')->with('success',
                    app()->getLocale() === 'vi'
                        ? 'Chuc mung! VIP da duoc kich hoat thanh cong!'
                        : 'Congratulations! VIP activated successfully!'
                );
            }
        } catch (\Exception $e) {
            return redirect()->route('subscription.index')->with('error', 'Payment verification failed');
        }

        return redirect()->route('subscription.index');
    }

    // Ki?m tra coupon qua AJAX
    public function checkCoupon(Request $request)
    {
        $code   = strtoupper(trim($request->input('code', '')));
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function($q) {
                $q->whereNull('max_uses')->orWhereRaw('used_count < max_uses');
            })
            ->first();

        if ($coupon) {
            return response()->json([
                'valid'          => true,
                'discount_type'  => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
            ]);
        }

        return response()->json([
            'valid'   => false,
            'message' => app()->getLocale() === 'vi' ? 'Ma khong hop le hoac da het han' : 'Invalid or expired coupon',
        ]);
    }

    private function activateVip(User $user, int $days): void
    {
        $currentExpiry = $user->vip_expires_at && $user->vip_expires_at->isFuture()
            ? $user->vip_expires_at
            : now();

        $user->update([
            'is_vip'          => true,
            'vip_expires_at'  => $currentExpiry->addDays($days),
        ]);
    }
}