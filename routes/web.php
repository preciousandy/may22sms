<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrdersController as AdminOrdersController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\User\BoostController;
use App\Http\Controllers\User\FavoriteController;
use App\Http\Controllers\User\FundingController;
use App\Http\Controllers\User\LogController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\OrdersController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\SecurityController;
use App\Http\Controllers\User\SmsController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\User\WalletController;
use App\Http\Middleware\CheckAccountStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/home', function () {
    return view('home');
});

Route::get('/api/football-matches', [\App\Http\Controllers\User\FootballMatchController::class, 'apiIndex']);
Route::get('/api/live-activity', [\App\Http\Controllers\User\LiveActivityController::class, 'getActivities']);

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Max 5 login attempts per minute

    Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,60'); // Max 3 registrations per hour

    // Google OAuth 2.0 Sign In / Sign Up
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/two-factor', [TwoFactorController::class, 'show']);
    Route::post('/two-factor', [TwoFactorController::class, 'verify']);
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resend']);

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email')->middleware('throttle:3,10'); // Max 3 requests per 10 mins

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update')->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']); // Fallback for simple link logout

// Payment callback routes (must work even if user session is lost)
Route::get('/funding/paymentpoint/callback', [FundingController::class, 'paymentPointCallback']);
Route::get('/funding/strollwallet/callback', [FundingController::class, 'strollwalletCallback']);
Route::get('/funding/pocketfi/callback', [FundingController::class, 'pocketfiCallback']);

// User Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1'); // Max 5 OTP attempts per minute
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend-otp')->middleware('throttle:1,1'); // Max 1 resend per minute
    Route::post('/admin/users/stop-impersonating', [\App\Http\Controllers\Admin\UserController::class, 'stopImpersonating']);
    Route::get('/admin/users/stop-impersonating', [\App\Http\Controllers\Admin\UserController::class, 'stopImpersonating']);
});

Route::middleware(['auth', CheckAccountStatus::class])->group(function () {
    Route::post('/currency', function (Request $request) {
        $request->validate([
            'currency' => 'required|in:USD,NGN',
        ]);

        session(['currency' => $request->currency]);

        return back();
    })->name('currency.set');

    Route::get('/football-matches', [\App\Http\Controllers\User\FootballMatchController::class, 'index']);

    Route::post('/api/reviews', [\App\Http\Controllers\User\ReviewController::class, 'store']);
    Route::get('/api/reviews/check', [\App\Http\Controllers\User\ReviewController::class, 'checkStatus']);

    // Daily Login Gift
    Route::get('/api/daily-gift/status', [\App\Http\Controllers\User\DailyGiftController::class, 'status']);
    Route::post('/api/daily-gift/claim', [\App\Http\Controllers\User\DailyGiftController::class, 'claim']);

    // Seasonal Celebrations & User Gift Claiming
    Route::get('/christmas-wonderland', [\App\Http\Controllers\User\SeasonalController::class, 'christmasHub'])->name('seasonal.christmas.hub');
    Route::post('/api/seasonal/gifts/{id}/claim', [\App\Http\Controllers\User\SeasonalController::class, 'claimGift'])->name('seasonal.gifts.claim');
    Route::post('/api/seasonal/advent/{day}/claim', [\App\Http\Controllers\User\SeasonalController::class, 'claimAdventDay'])->name('seasonal.advent.claim');
    Route::post('/api/seasonal/creations/save', [\App\Http\Controllers\User\SeasonalController::class, 'saveCreation'])->name('seasonal.creations.save');
    Route::post('/api/seasonal/challenges/{id}/claim', [\App\Http\Controllers\User\SeasonalController::class, 'claimChallenge'])->name('seasonal.challenges.claim');
    Route::post('/api/seasonal/preferences/animations', [\App\Http\Controllers\User\SeasonalController::class, 'toggleAnimations'])->name('seasonal.preferences.animations');
    Route::post('/api/seasonal/announcements/{id}/dismiss', [\App\Http\Controllers\User\SeasonalController::class, 'dismissAnnouncement'])->name('seasonal.announcements.dismiss');
    Route::get('/api/seasonal/claims/history', [\App\Http\Controllers\User\SeasonalController::class, 'history'])->name('seasonal.claims.history');

    Route::get('/dashboard', function () {
        $user = auth()->user();

        $smsNumbersCount = $user->smsOrders()->count();
        $logsPurchasedCount = (int) $user->logOrders()->sum('total_items');
        $boostsCount = $user->boostOrders()->count();

        $recentSms = $user->smsOrders()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'sms',
                    'title' => $order->product ?: 'SMS Verification',
                    'meta' => $order->phone_number ? ('+'.$order->phone_number) : ('Order #'.$order->order_id),
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            })
            ->toBase();

        $recentLogs = $user->logOrders()
            ->with('credentials.item')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                $firstItemTitle = optional(optional($order->credentials->first())->item)->title;

                return [
                    'type' => 'logs',
                    'title' => $firstItemTitle ?: 'Logs Purchase',
                    'meta_items' => (int) $order->total_items,
                    'meta_amount_usd' => (float) $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            })
            ->toBase();

        $recentBoosts = $user->boostOrders()
            ->with('service')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'boost',
                    'title' => $order->service->name ?? 'Boost Order',
                    'meta_items' => (int) $order->quantity,
                    'meta_amount_usd' => (float) $order->charge,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            })
            ->toBase();

        $recentActivities = $recentSms
            ->merge($recentLogs)
            ->merge($recentBoosts)
            ->sortByDesc('created_at')
            ->values()
            ->take(8);

        return view('dashboard', compact(
            'smsNumbersCount',
            'logsPurchasedCount',
            'boostsCount',
            'recentActivities'
        ));
    })->name('dashboard');

    Route::get('/sms-verify', [SmsController::class, 'index']);
    Route::get('/api/sms/services', [SmsController::class, 'getServices']);
    Route::get('/api/sms/price-tiers', [SmsController::class, 'getPriceTiers']);
    Route::post('/api/sms/buy', [SmsController::class, 'buyNumber']);
    Route::get('/api/sms/check/{id}', [SmsController::class, 'checkSms']);
    Route::post('/api/sms/cancel/{id}', [SmsController::class, 'cancelOrder']);

    // Logs Store
    Route::get('/logs-store', [LogController::class, 'index']);
    Route::get('/logs-store/cart', [LogController::class, 'cart'])->name('logs.cart');
    Route::post('/logs-store/cart/{id}', [LogController::class, 'addToCart']);
    Route::delete('/logs-store/cart/{id}', [LogController::class, 'removeFromCart']);
    Route::post('/logs-store/checkout', [LogController::class, 'checkout']);
    Route::post('/logs-store/buy/{id}', [LogController::class, 'buy']);

    Route::get('/social-boosting', [BoostController::class, 'index']);
    Route::post('/social-boosting/order', [BoostController::class, 'placeOrder']);
    Route::get('/api/boost/check/{id}', [BoostController::class, 'checkOrder']);

    Route::get('/funding', [FundingController::class, 'index'])->name('funding');
    Route::post('/funding', [FundingController::class, 'store']);
    Route::post('/funding/{id}/cancel', [FundingController::class, 'cancel'])->name('funding.cancel');
    Route::post('/funding/{id}/check-status', [FundingController::class, 'checkStatus'])->name('funding.check-status');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');

    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::post('/profile', [ProfileController::class, 'update']);

    Route::get('/orders', [OrdersController::class, 'index']);
    Route::post('/orders/report', [\App\Http\Controllers\User\OrderReportController::class, 'store']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);

    Route::get('/tickets', [SupportTicketController::class, 'index']);
    Route::post('/tickets', [SupportTicketController::class, 'store']);
    Route::get('/tickets/{id}', [SupportTicketController::class, 'show']);
    Route::post('/tickets/{id}/reply', [SupportTicketController::class, 'reply']);
    Route::post('/tickets/{id}/close', [SupportTicketController::class, 'close']);
    Route::post('/tickets/{id}/reopen', [SupportTicketController::class, 'reopen']);
    Route::get('/tickets/{id}/messages', [SupportTicketController::class, 'messagesJson']);

    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);

    Route::get('/security', [SecurityController::class, 'index']);
    Route::post('/security/2fa/enable', [SecurityController::class, 'enableTwoFactor']);
    Route::post('/security/2fa/disable', [SecurityController::class, 'disableTwoFactor']);
    Route::post('/security/logout-other-devices', [SecurityController::class, 'logoutOtherDevices']);

    Route::get('/sporty-bet-codes', [\App\Http\Controllers\User\SportyBetCodeController::class, 'index']);
    Route::post('/sporty-bet-codes/{code}/purchase', [\App\Http\Controllers\User\SportyBetCodeController::class, 'purchase']);

    // Tutorials & How-To-Use Guides Center
    Route::get('/tutorials', [\App\Http\Controllers\User\TutorialController::class, 'index'])->name('tutorials');
    Route::get('/how-it-works', [\App\Http\Controllers\User\TutorialController::class, 'index'])->name('how-it-works');

    // Save Small Small (Target & Goal Savings)
    Route::get('/savings', [\App\Http\Controllers\User\SavingsController::class, 'index'])->name('savings.index');
    Route::get('/savings/create', [\App\Http\Controllers\User\SavingsController::class, 'create'])->name('savings.create');
    Route::post('/savings', [\App\Http\Controllers\User\SavingsController::class, 'store'])->name('savings.store');
    Route::get('/savings/calculator', [\App\Http\Controllers\User\SavingsController::class, 'calculator'])->name('savings.calculator');
    Route::get('/savings/{id}', [\App\Http\Controllers\User\SavingsController::class, 'show'])->name('savings.show');
    Route::post('/savings/{id}/contribute', [\App\Http\Controllers\User\SavingsController::class, 'contribute'])->name('savings.contribute');
    Route::post('/savings/{id}/break', [\App\Http\Controllers\User\SavingsController::class, 'breakPlan'])->name('savings.break');

    // Digital Utilities & Bills (BigiSub Integration)
    Route::get('/services', [\App\Http\Controllers\User\UtilityController::class, 'index'])->name('utilities.index');
    Route::get('/user/utilities', [\App\Http\Controllers\User\UtilityController::class, 'index'])->name('user.utilities.index');
    Route::get('/services/airtime', [\App\Http\Controllers\User\UtilityController::class, 'airtime'])->name('utilities.airtime');
    Route::get('/services/data', [\App\Http\Controllers\User\UtilityController::class, 'data'])->name('utilities.data');
    Route::get('/services/electricity', [\App\Http\Controllers\User\UtilityController::class, 'electricity'])->name('utilities.electricity');
    Route::get('/services/betting', [\App\Http\Controllers\User\UtilityController::class, 'betting'])->name('utilities.betting');
    Route::get('/services/airtime-data', [\App\Http\Controllers\User\UtilityController::class, 'index'])->name('utilities.airtime-data');
    Route::get('/services/history', [\App\Http\Controllers\User\UtilityController::class, 'history'])->name('utilities.history');
    Route::get('/user/utilities/history', [\App\Http\Controllers\User\UtilityController::class, 'history'])->name('user.utilities.history');
    Route::get('/services/receipt/{reference}', [\App\Http\Controllers\User\UtilityController::class, 'receipt'])->name('utilities.receipt');
    Route::get('/user/utilities/receipt/{reference}', [\App\Http\Controllers\User\UtilityController::class, 'receipt'])->name('user.utilities.receipt');

    Route::get('/api/utilities/plans', [\App\Http\Controllers\User\UtilityController::class, 'getPlans'])->name('utilities.api.plans');
    Route::get('/api/utilities/quote', [\App\Http\Controllers\User\UtilityController::class, 'quote'])->name('utilities.api.quote');
    Route::post('/api/utilities/verify-meter', [\App\Http\Controllers\User\UtilityController::class, 'verifyMeter'])->name('utilities.api.verify-meter');
    Route::post('/api/utilities/verify-betting', [\App\Http\Controllers\User\UtilityController::class, 'verifyBetting'])->name('utilities.api.verify-betting');

    Route::post('/services/airtime/purchase', [\App\Http\Controllers\User\UtilityController::class, 'purchaseAirtime'])->name('utilities.purchase.airtime');
    Route::post('/services/data/purchase', [\App\Http\Controllers\User\UtilityController::class, 'purchaseData'])->name('utilities.purchase.data');
    Route::post('/services/electricity/pay', [\App\Http\Controllers\User\UtilityController::class, 'payElectricity'])->name('utilities.pay.electricity');
    Route::post('/services/betting/fund', [\App\Http\Controllers\User\UtilityController::class, 'fundBetting'])->name('utilities.fund.betting');

    // Receive Crypto & Convert to Naira (Quidax Integration)
    Route::get('/services/crypto-to-naira', [\App\Http\Controllers\User\CryptoController::class, 'index'])->name('services.crypto-to-naira');
    Route::post('/services/crypto-to-naira/quote', [\App\Http\Controllers\User\CryptoController::class, 'quote'])->name('crypto.quote');
    Route::post('/services/crypto-to-naira/create', [\App\Http\Controllers\User\CryptoController::class, 'create'])->name('crypto.create');
    Route::get('/services/crypto-to-naira/status/{reference}', [\App\Http\Controllers\User\CryptoController::class, 'status'])->name('crypto.status');
});

// Admin Protected Routes
Route::middleware(['auth', CheckAccountStatus::class, 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Save Small Small Admin Management
    Route::get('/savings', [\App\Http\Controllers\Admin\SavingsController::class, 'index'])->name('admin.savings.index');
    Route::post('/savings/toggle-global-interest', [\App\Http\Controllers\Admin\SavingsController::class, 'toggleGlobalInterest'])->name('admin.savings.toggle-global-interest');
    Route::get('/savings/categories', [\App\Http\Controllers\Admin\SavingsController::class, 'categories'])->name('admin.savings.categories');
    Route::post('/savings/categories', [\App\Http\Controllers\Admin\SavingsController::class, 'storeCategory'])->name('admin.savings.categories.store');
    Route::post('/savings/categories/{id}', [\App\Http\Controllers\Admin\SavingsController::class, 'updateCategory'])->name('admin.savings.categories.update');
    Route::post('/savings/categories/{id}/toggle-interest', [\App\Http\Controllers\Admin\SavingsController::class, 'toggleCategoryInterest'])->name('admin.savings.categories.toggle-interest');
    Route::post('/savings/categories/{id}/break-fee', [\App\Http\Controllers\Admin\SavingsController::class, 'updateBreakFee'])->name('admin.savings.categories.update-break-fee');
    Route::get('/savings/reconciliation', [\App\Http\Controllers\Admin\SavingsController::class, 'reconciliation'])->name('admin.savings.reconciliation');
    Route::get('/savings/{id}', [\App\Http\Controllers\Admin\SavingsController::class, 'show'])->name('admin.savings.show');

    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index']);
    Route::delete('/reviews/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy']);
    Route::get('/mail', [\App\Http\Controllers\Admin\MailController::class, 'index']);
    Route::match(['get'], '/mail/send-single', function () { return redirect('/admin/mail'); });
    Route::post('/mail/send-single', [\App\Http\Controllers\Admin\MailController::class, 'sendSingle']);
    Route::match(['get'], '/mail/send-bulk', function () { return redirect('/admin/mail'); });
    Route::post('/mail/send-bulk', [\App\Http\Controllers\Admin\MailController::class, 'sendBulk']);
    Route::get('/football-matches', [\App\Http\Controllers\Admin\FootballMatchController::class, 'index']);
    Route::post('/football-matches', [\App\Http\Controllers\Admin\FootballMatchController::class, 'store']);
    Route::post('/football-matches/sync', [\App\Http\Controllers\Admin\FootballMatchController::class, 'sync']);
    Route::post('/football-matches/settings', [\App\Http\Controllers\Admin\FootballMatchController::class, 'updateSettings']);
    Route::post('/football-matches/{id}', [\App\Http\Controllers\Admin\FootballMatchController::class, 'update']);
    Route::delete('/football-matches/{id}', [\App\Http\Controllers\Admin\FootballMatchController::class, 'destroy']);

    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::post('/users/create', [\App\Http\Controllers\Admin\UserController::class, 'store']);
    Route::post('/users/bulk-delete', [\App\Http\Controllers\Admin\UserController::class, 'bulkDestroy']);
    Route::get('/users/export/csv', [\App\Http\Controllers\Admin\UserController::class, 'exportCsv']);
    Route::get('/users/export/print', [\App\Http\Controllers\Admin\UserController::class, 'exportPrint']);
    Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show']);
    Route::post('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update']);
    Route::delete('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy']);
    Route::post('/users/{id}/delete', [\App\Http\Controllers\Admin\UserController::class, 'destroy']);
    Route::post('/users/{id}/password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword']);
    Route::post('/users/{id}/wallet-adjust', [\App\Http\Controllers\Admin\UserController::class, 'adjustWallet']);
    Route::post('/users/{id}/impersonate', [\App\Http\Controllers\Admin\UserController::class, 'impersonate']);
    Route::post('/users/{id}/force-logout', [\App\Http\Controllers\Admin\UserController::class, 'forceLogout']);
    Route::post('/users/{id}/notify', [\App\Http\Controllers\Admin\UserController::class, 'notify']);
    Route::post('/users/{id}/notifications/mark-all-read', [\App\Http\Controllers\Admin\UserController::class, 'markAllNotificationsRead']);

    Route::get('/transactions', [\App\Http\Controllers\Admin\SystemTransactionController::class, 'index']);
    Route::post('/transactions/backfill', [\App\Http\Controllers\Admin\SystemTransactionController::class, 'backfill']);
    Route::get('/transactions/{id}', [\App\Http\Controllers\Admin\SystemTransactionController::class, 'show']);
    Route::post('/transactions/{id}', [\App\Http\Controllers\Admin\SystemTransactionController::class, 'update']);
    Route::post('/transactions/{id}/approve', [\App\Http\Controllers\Admin\SystemTransactionController::class, 'approve']);
    Route::post('/transactions/{id}/revert', [\App\Http\Controllers\Admin\SystemTransactionController::class, 'revert']);
    Route::delete('/transactions/{id}', [\App\Http\Controllers\Admin\SystemTransactionController::class, 'destroy']);

    Route::get('/orders', [AdminOrdersController::class, 'index']);
    Route::post('/orders/refund', [AdminOrdersController::class, 'refundOrder']);
    Route::post('/orders/cancel', [AdminOrdersController::class, 'cancelOrder']);
    Route::post('/orders/approve-funding', [AdminOrdersController::class, 'approveFunding']);
    Route::post('/orders/reverse-funding', [AdminOrdersController::class, 'reverseFunding']);

    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index']);
    Route::post('/reports/{id}/action', [\App\Http\Controllers\Admin\ReportController::class, 'action']);

    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/maintenance', [SettingsController::class, 'maintenance']);
    Route::get('/settings/storage-link', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            return redirect('/admin/settings')->with('success', 'Storage link created successfully!');
        } catch (\Throwable $e) {
            return redirect('/admin/settings')->with('error', 'Storage link error: ' . $e->getMessage());
        }
    });
    Route::post('/settings/sync-countries', [SettingsController::class, 'syncCountries']);

    // Admin Service Availability Controls
    Route::get('/services', [\App\Http\Controllers\Admin\ServiceManagementController::class, 'index'])->name('admin.services.index');
    Route::post('/services/{key}/toggle', [\App\Http\Controllers\Admin\ServiceManagementController::class, 'toggle'])->name('admin.services.toggle');

    Route::get('/charges', [\App\Http\Controllers\Admin\ChargesController::class, 'index']);
    Route::post('/charges', [\App\Http\Controllers\Admin\ChargesController::class, 'update']);

    // Admin Logs Management
    Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index']);
    Route::post('/logs', [\App\Http\Controllers\Admin\LogController::class, 'store']);
    Route::post('/logs/bulk', [\App\Http\Controllers\Admin\LogController::class, 'bulkStore']);
    Route::post('/logs/{id}/update', [\App\Http\Controllers\Admin\LogController::class, 'updateItem']);
    Route::post('/logs/{id}/status', [\App\Http\Controllers\Admin\LogController::class, 'updateStatus']);
    Route::post('/logs/{id}/stock', [\App\Http\Controllers\Admin\LogController::class, 'addStock']);
    Route::delete('/logs/{id}/stock', [\App\Http\Controllers\Admin\LogController::class, 'clearStock']);
    Route::post('/logs/{id}/in-stock', [\App\Http\Controllers\Admin\LogController::class, 'markInStock']);

    // Admin Log Category Videos
    Route::get('/log-videos', [\App\Http\Controllers\Admin\LogVideoController::class, 'index']);
    Route::post('/log-videos', [\App\Http\Controllers\Admin\LogVideoController::class, 'store']);
    Route::post('/log-videos/{id}/update', [\App\Http\Controllers\Admin\LogVideoController::class, 'update']);
    Route::post('/log-videos/{id}/toggle', [\App\Http\Controllers\Admin\LogVideoController::class, 'toggle']);
    Route::delete('/log-videos/{id}', [\App\Http\Controllers\Admin\LogVideoController::class, 'destroy']);

    Route::get('/boosting', [\App\Http\Controllers\Admin\BoostController::class, 'index']);
    Route::post('/boosting/settings', [\App\Http\Controllers\Admin\BoostController::class, 'updateMarkup']);
    Route::post('/boosting/category-markups', [\App\Http\Controllers\Admin\BoostController::class, 'updateCategoryMarkups']);
    Route::post('/boosting/auto-sync', [\App\Http\Controllers\Admin\BoostController::class, 'updateAutoSync']);
    Route::post('/boosting/sync', [\App\Http\Controllers\Admin\BoostController::class, 'syncServices']);
    Route::post('/boosting/services/{id}', [\App\Http\Controllers\Admin\BoostController::class, 'updateService']);
    Route::post('/boosting/services/{id}/reset-failures', [\App\Http\Controllers\Admin\BoostController::class, 'resetServiceFailures']);
    Route::post('/boosting/reset-all-failures', [\App\Http\Controllers\Admin\BoostController::class, 'bulkResetFailures']);

    Route::get('/funding', [\App\Http\Controllers\Admin\FundingController::class, 'index']);
    Route::post('/funding/{id}/approve', [\App\Http\Controllers\Admin\FundingController::class, 'approve']);
    Route::post('/funding/{id}/reject', [\App\Http\Controllers\Admin\FundingController::class, 'reject']);

    Route::get('/tickets', [\App\Http\Controllers\Admin\SupportTicketController::class, 'index']);
    Route::get('/tickets/{id}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'show']);
    Route::post('/tickets/{id}/reply', [\App\Http\Controllers\Admin\SupportTicketController::class, 'reply']);
    Route::post('/tickets/{id}/close', [\App\Http\Controllers\Admin\SupportTicketController::class, 'close']);

    Route::get('/promo-codes', [PromoCodeController::class, 'index']);
    Route::post('/promo-codes', [PromoCodeController::class, 'store']);
    Route::post('/promo-codes/{id}/toggle', [PromoCodeController::class, 'toggle']);
    Route::post('/promo-codes/{id}/public', [PromoCodeController::class, 'togglePublic']);
    Route::post('/promo-codes/{id}/broadcast', [PromoCodeController::class, 'broadcast']);

    Route::get('/sporty-bet-codes', [\App\Http\Controllers\Admin\SportyBetCodeController::class, 'index']);
    Route::post('/sporty-bet-codes', [\App\Http\Controllers\Admin\SportyBetCodeController::class, 'store']);
    Route::post('/sporty-bet-codes/{code}/toggle', [\App\Http\Controllers\Admin\SportyBetCodeController::class, 'toggle']);
    Route::delete('/sporty-bet-codes/{code}', [\App\Http\Controllers\Admin\SportyBetCodeController::class, 'destroy']);

    // Digital Utilities (BigiSub) Admin Management
    Route::get('/utilities', [\App\Http\Controllers\Admin\UtilityAdminController::class, 'index'])->name('admin.utilities.index');
    Route::get('/utilities/{id}', [\App\Http\Controllers\Admin\UtilityAdminController::class, 'show'])->name('admin.utilities.show');
    Route::post('/utilities/settings', [\App\Http\Controllers\Admin\UtilityAdminController::class, 'updateSettings'])->name('admin.utilities.settings');
    Route::post('/utilities/{id}/reconcile', [\App\Http\Controllers\Admin\UtilityAdminController::class, 'reconcile'])->name('admin.utilities.reconcile');
    Route::post('/utilities/{id}/refund', [\App\Http\Controllers\Admin\UtilityAdminController::class, 'refund'])->name('admin.utilities.refund');

    // Quidax Crypto Off-Ramp Admin Management
    Route::get('/crypto', [\App\Http\Controllers\Admin\CryptoAdminController::class, 'index'])->name('admin.crypto.index');
    Route::post('/crypto/{id}/reconcile', [\App\Http\Controllers\Admin\CryptoAdminController::class, 'reconcile'])->name('admin.crypto.reconcile');
    Route::post('/crypto/toggle-service', [\App\Http\Controllers\Admin\CryptoAdminController::class, 'toggleService'])->name('admin.crypto.toggle');

    // Seasonal Modes, Animations & Celebration Gifts Admin Management
    Route::get('/seasonal', [\App\Http\Controllers\Admin\SeasonalController::class, 'index'])->name('admin.seasonal.index');
    Route::post('/seasonal/modes', [\App\Http\Controllers\Admin\SeasonalController::class, 'storeMode'])->name('admin.seasonal.modes.store');
    Route::post('/seasonal/modes/deactivate-all', [\App\Http\Controllers\Admin\SeasonalController::class, 'deactivateAllModes'])->name('admin.seasonal.modes.deactivate-all');
    Route::post('/seasonal/modes/{id}', [\App\Http\Controllers\Admin\SeasonalController::class, 'updateMode'])->name('admin.seasonal.modes.update');
    Route::post('/seasonal/modes/{id}/toggle', [\App\Http\Controllers\Admin\SeasonalController::class, 'toggleMode'])->name('admin.seasonal.modes.toggle');
    Route::delete('/seasonal/modes/{id}', [\App\Http\Controllers\Admin\SeasonalController::class, 'destroyMode'])->name('admin.seasonal.modes.destroy');

    Route::post('/seasonal/gifts', [\App\Http\Controllers\Admin\SeasonalController::class, 'storeGift'])->name('admin.seasonal.gifts.store');
    Route::post('/seasonal/gifts/{id}', [\App\Http\Controllers\Admin\SeasonalController::class, 'updateGift'])->name('admin.seasonal.gifts.update');
    Route::post('/seasonal/gifts/{id}/toggle', [\App\Http\Controllers\Admin\SeasonalController::class, 'toggleGift'])->name('admin.seasonal.gifts.toggle');
    Route::post('/seasonal/gifts/{id}/distribute', [\App\Http\Controllers\Admin\SeasonalController::class, 'distributeGift'])->name('admin.seasonal.gifts.distribute');
    Route::delete('/seasonal/gifts/{id}', [\App\Http\Controllers\Admin\SeasonalController::class, 'destroyGift'])->name('admin.seasonal.gifts.destroy');

    Route::post('/seasonal/announcements', [\App\Http\Controllers\Admin\SeasonalController::class, 'storeAnnouncement'])->name('admin.seasonal.announcements.store');
    Route::post('/seasonal/announcements/{id}/toggle', [\App\Http\Controllers\Admin\SeasonalController::class, 'toggleAnnouncement'])->name('admin.seasonal.announcements.toggle');
    Route::post('/seasonal/announcements/{id}/broadcast', [\App\Http\Controllers\Admin\SeasonalController::class, 'broadcastAnnouncement'])->name('admin.seasonal.announcements.broadcast');
    Route::delete('/seasonal/announcements/{id}', [\App\Http\Controllers\Admin\SeasonalController::class, 'destroyAnnouncement'])->name('admin.seasonal.announcements.destroy');

    // Interactive Christmas Admin Endpoints (Advent Calendar, Challenges, Badges & Feature Toggles)
    Route::post('/seasonal/advent/{id}', [\App\Http\Controllers\Admin\SeasonalController::class, 'updateAdventDay'])->name('admin.seasonal.advent.update');
    Route::post('/seasonal/challenges', [\App\Http\Controllers\Admin\SeasonalController::class, 'storeChallenge'])->name('admin.seasonal.challenges.store');
    Route::post('/seasonal/challenges/{id}/toggle', [\App\Http\Controllers\Admin\SeasonalController::class, 'toggleChallenge'])->name('admin.seasonal.challenges.toggle');
    Route::delete('/seasonal/challenges/{id}', [\App\Http\Controllers\Admin\SeasonalController::class, 'destroyChallenge'])->name('admin.seasonal.challenges.destroy');
    Route::post('/seasonal/badges', [\App\Http\Controllers\Admin\SeasonalController::class, 'storeBadge'])->name('admin.seasonal.badges.store');
    Route::post('/seasonal/badges/{id}/toggle', [\App\Http\Controllers\Admin\SeasonalController::class, 'toggleBadge'])->name('admin.seasonal.badges.toggle');
    Route::post('/seasonal/interactive-features', [\App\Http\Controllers\Admin\SeasonalController::class, 'updateInteractiveFeatures'])->name('admin.seasonal.interactive.update');
    Route::post('/seasonal/toggle-christmas-button', [\App\Http\Controllers\Admin\SeasonalController::class, 'toggleChristmasButton'])->name('admin.seasonal.toggle-christmas-button');
});

// Quidax Webhook Endpoints
Route::post('/api/webhooks/quidax', [\App\Http\Controllers\QuidaxWebhookController::class, 'handle']);
Route::post('/webhooks/quidax', [\App\Http\Controllers\QuidaxWebhookController::class, 'handle']);
Route::post('/quidax/webhook', [\App\Http\Controllers\QuidaxWebhookController::class, 'handle']);


Route::get('/test-mail', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('This is a test email from May22SMS to verify SMTP mail configuration.', function ($message) {
            $message->to(request('to', 'andyprecious6@gmail.com'))
                ->subject('May22SMS SMTP Connection Test');
        });
        return response()->json(['success' => true, 'message' => 'Test email sent successfully!']);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'SMTP error: ' . $e->getMessage(),
            'env_mailer' => config('mail.default'),
            'env_host' => config('mail.mailers.smtp.host'),
            'env_port' => config('mail.mailers.smtp.port'),
            'env_username' => config('mail.mailers.smtp.username'),
            'env_encryption' => config('mail.mailers.smtp.encryption'),
        ]);
    }
});
