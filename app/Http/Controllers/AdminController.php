<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VipPlan;
use App\Models\Coupon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users'       => User::count(),
            'vip_users'   => User::where('is_vip', true)->count(),
            'films'       => 0,
            'coupons'     => Coupon::where('is_active', true)->count(),
            'revenue_usd' => 0,
            'revenue_vnd' => 0,
        ];
        return view('admin.index', compact('stats'));
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function grantVip(Request $request, User $user)
    {
        $days = (int) $request->input('days', 30);
        $current = $user->vip_expires_at && $user->vip_expires_at->isFuture()
            ? $user->vip_expires_at : now();
        $user->update([
            'is_vip'         => true,
            'vip_expires_at' => $current->addDays($days),
        ]);
        return back()->with('success', "Granted VIP {$days} days to {$user->name}");
    }

    // -- VIP Plans -----------------------------------------
    public function plans()
    {
        $plans = VipPlan::orderBy('days')->get();
        return view('admin.plans', compact('plans'));
    }

    public function updatePlan(Request $request, VipPlan $plan)
    {
        $request->validate([
            'name'        => 'required|string|max:50',
            'price'       => 'required|numeric|min:0',
            'days'        => 'required|integer|min:1',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        $plan->update([
            'name'        => $request->name,
            'price'       => $request->price,
            'days'        => $request->days,
            'is_featured' => $request->boolean('is_featured'),
            'is_active'   => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Plan updated successfully!');
    }

    // -- Coupons --------------------------------------------
    public function coupons()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();
        return view('admin.coupons', compact('coupons'));
    }

    public function createCoupon(Request $request)
    {
        $request->validate([
            'code'           => 'required|string|max:20|unique:coupons,code',
            'discount_type'  => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_uses'       => 'nullable|integer|min:1',
            'expires_at'     => 'nullable|date',
        ]);

        Coupon::create([
            'code'           => strtoupper(trim($request->code)),
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'max_uses'       => $request->max_uses ?: null,
            'expires_at'     => $request->expires_at ?: null,
            'is_active'      => true,
        ]);

        return back()->with('success', 'Coupon created successfully!');
    }

    public function toggleCoupon(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', 'Coupon updated!');
    }

    public function deleteCoupon(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon deleted!');
    }

    public function transactions()
    {
        $transactions = \App\Models\Subscription::with('user', 'plan')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.transactions', compact('transactions'));
    }
}