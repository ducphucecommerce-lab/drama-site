<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VipPlan;
use App\Models\Coupon;

class PaymentController extends Controller
{
    public function index()
    {
        $plans = VipPlan::where('is_active', true)->orderBy('days')->get();
        return view('subscription.index', compact('plans'));
    }

    public function checkout(Request $request)
    {
        $planKey  = $request->input('plan', '1month');
        $planData = VipPlan::where('key', $planKey)->where('is_active', true)->first();

        if (!$planData) {
            return redirect()->route('subscription.index')->with('error', 'Invalid plan');
        }

        $price      = (float) $planData->price;
        $couponCode = strtoupper(trim($request->input('coupon_code', '')));
        $coupon     = null;
        $discount   = 0;

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
                    $discount = min((float)$coupon->discount_value, $price);
                }
                $price = max(0, $price - $discount);
            }
        }

        // Free via coupon
        if ($price <= 0 && $coupon) {
            $this->activateVip(auth()->user(), $planData->days);
            $coupon->increment('used_count');
            return redirect()->route('home')->with('success',
                app()->getLocale() === 'vi' ? 'VIP da duoc kich hoat!' : 'VIP activated successfully!'
            );
        }

        // Stripe
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'usd',
                    'product_data' => ['name' => config('app.name') . ' VIP - ' . $planData->name],
                    'unit_amount'  => (int)($price * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => route('payment.success') . '?plan=' . $planKey . '&coupon=' . $couponCode . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('subscription.index'),
            'metadata'    => [
                'user_id'     => auth()->id(),
                'plan'        => $planKey,
                'coupon_code' => $couponCode,
            ],
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $planKey    = $request->input('plan', '1month');
        $sessionId  = $request->input('session_id');
        $couponCode = strtoupper(trim($request->input('coupon', '')));
        $planData   = VipPlan::where('key', $planKey)->first();

        if (!$sessionId || !$planData) return redirect()->route('home');

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $this->activateVip(auth()->user(), $planData->days);
                if ($couponCode) {
                    Coupon::where('code', $couponCode)->increment('used_count');
                }
                return redirect()->route('home')->with('success',
                    app()->getLocale() === 'vi'
                        ? 'Chuc mung! VIP da duoc kich hoat!'
                        : 'Congratulations! VIP activated successfully!'
                );
            }
        } catch (\Exception $e) {
            return redirect()->route('subscription.index')->with('error', 'Payment verification failed');
        }

        return redirect()->route('subscription.index');
    }

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
        $current = $user->vip_expires_at && $user->vip_expires_at->isFuture()
            ? $user->vip_expires_at
            : now();
        $user->update([
            'is_vip'         => true,
            'vip_expires_at' => $current->addDays($days),
        ]);
    }
}