<?php
use App\Http\Controllers\{AdminController, AuthController, FilmController, PaymentController};
use Illuminate\Support\Facades\Route;

// ── Public routes ─────────────────────────────────────────
Route::get('/',              [FilmController::class, 'index'])->name('home');
Route::get('/search',        [FilmController::class, 'search'])->name('films.search');
Route::get('/genre/{genre}', [FilmController::class, 'genre'])->name('films.genre');
Route::get('/film/{id}',     [FilmController::class, 'detail'])->name('films.detail');

// Xem phim: tập 1-3 miễn phí, còn lại cần VIP
Route::get('/watch/{id}',    [FilmController::class, 'watch'])->name('films.watch');

// ── Auth routes ───────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',          [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',         [AuthController::class, 'login']);
    Route::get('/register',       [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',      [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout',        [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile',        [AuthController::class, 'profile'])->name('profile');

    // ── Subscription ─────────────────────────────────────
    Route::get('/vip',                    [PaymentController::class, 'index'])->name('subscription.index');
    Route::post('/payment/vnpay',         [PaymentController::class, 'vnpayCheckout'])->name('payment.vnpay');
    Route::post('/payment/stripe',        [PaymentController::class, 'stripeCheckout'])->name('payment.stripe');
    Route::get('/payment/stripe/success', [PaymentController::class, 'stripeSuccess'])->name('payment.stripe.success');
});

// VNPay return (không cần auth vì VNPay redirect về)
Route::get('/payment/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');

// Stripe Webhook (bỏ CSRF)
Route::post('/webhook/stripe', [PaymentController::class, 'stripeWebhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ── Admin routes ──────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                              [AdminController::class, 'index'])->name('index');
    Route::get('/users',                         [AdminController::class, 'users'])->name('users');
    Route::get('/transactions',                  [AdminController::class, 'transactions'])->name('transactions');
    Route::post('/users/{user}/grant-vip',       [AdminController::class, 'grantVip'])->name('grant-vip');
    Route::post('/users/{user}/revoke-vip',      [AdminController::class, 'revokeVip'])->name('revoke-vip');
});
