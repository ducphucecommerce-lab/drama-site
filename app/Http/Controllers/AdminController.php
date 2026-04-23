<?php
namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Dashboard
    public function index()
    {
        $stats = [
            'total_users'   => User::count(),
            'vip_users'     => User::where('is_vip', true)->where('vip_expires_at', '>', now())->count(),
            'revenue_vnd'   => Subscription::where('status', 'paid')->where('currency', 'VND')->sum('amount'),
            'revenue_usd'   => Subscription::where('status', 'paid')->where('currency', 'USD')->sum('amount'),
            'today_payments'=> Subscription::where('status', 'paid')->whereDate('created_at', today())->count(),
        ];

        $recentTransactions = Subscription::with('user')->where('status', 'paid')
            ->latest()->take(10)->get();

        return view('admin.index', compact('stats', 'recentTransactions'));
    }

    // Danh sách users
    public function users(Request $request)
    {
        $search = $request->get('q');
        $users  = User::when($search, fn($q) => $q->where('email', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%"))
            ->latest()->paginate(20);

        return view('admin.users', compact('users', 'search'));
    }

    // Tặng VIP thủ công
    public function grantVip(Request $request, User $user)
    {
        $days = (int) $request->get('days', 30);
        $user->activateVip($days);

        Subscription::create([
            'user_id'        => $user->id,
            'plan'           => 'vip',
            'payment_method' => 'manual',
            'transaction_id' => 'MANUAL_' . strtoupper(uniqid()),
            'amount'         => 0,
            'currency'       => 'VND',
            'status'         => 'paid',
            'starts_at'      => now(),
            'expires_at'     => now()->addDays($days),
        ]);

        return back()->with('success', "Đã tặng VIP {$days} ngày cho {$user->name}");
    }

    // Thu hồi VIP
    public function revokeVip(User $user)
    {
        $user->update(['is_vip' => false, 'vip_expires_at' => null]);
        return back()->with('success', "Đã thu hồi VIP của {$user->name}");
    }

    // Danh sách giao dịch
    public function transactions(Request $request)
    {
        $transactions = Subscription::with('user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->method, fn($q) => $q->where('payment_method', $request->method))
            ->latest()->paginate(25);

        return view('admin.transactions', compact('transactions'));
    }
}
