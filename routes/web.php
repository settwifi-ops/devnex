<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\Auth\ResetPassword;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Billing;
use App\Http\Livewire\Profile;
use App\Http\Livewire\Tables;
use App\Http\Livewire\StaticSignIn;
use App\Http\Livewire\StaticSignUp;
use App\Http\Livewire\Rtl;
use App\Http\Livewire\LaravelExamples\UserProfile;
use App\Http\Livewire\LaravelExamples\UserManagement;
use App\Http\Livewire\VirtualReality;
use App\Http\Livewire\PortfolioDashboard;
use App\Http\Livewire\SmartSignals;
use Illuminate\Support\Facades\Broadcast;

// Controllers
use App\Http\Controllers\Sector2Controller;
use App\Http\Controllers\SignalController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\Dashboard\MarketDashboardController;
use App\Http\Controllers\AiSignalController;
use App\Http\Controllers\NavbarController;
use App\Http\Controllers\AiDecisionController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\AuthController;



// 📄 routes/web.php
use App\Http\Livewire\RealTradingPage;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});
// ===== BROADCASTING ROUTES =====
// routes/web.php - Manual broadcasting route
Route::post('/broadcasting/auth', function() {
    $user = auth()->user();
    $channelName = request()->channel_name;
    $socketId = request()->socket_id;
    
    if (preg_match('/^private-user-(\d+)$/', $channelName, $matches)) {
        $channelUserId = (int) $matches[1];
        $authenticatedUserId = (int) $user->id;
        
        if ($channelUserId === $authenticatedUserId) {
            $auth = [
                'auth' => config('broadcasting.connections.pusher.key') . ':' . hash_hmac(
                    'sha256',
                    $socketId . ':' . $channelName,
                    config('broadcasting.connections.pusher.secret')
                )
            ];
            return response()->json($auth);
        }
    }
    return response()->json(['error' => 'Forbidden'], 403);
})->middleware(['web', 'auth']);
// ==================== PUBLIC ROUTES (GUEST) ====================
Route::middleware('guest')->group(function () {
    Route::get('/register', Register::class)->name('register');
    Route::get('/login', Login::class)->name('login');
    Route::get('/login/forgot-password', ForgotPassword::class)->name('forgot-password');
    Route::get('/reset-password/{id}', ResetPassword::class)->name('reset-password')->middleware('signed');
});

// ==================== PROTECTED ROUTES (AUTH) ====================

// Routes yang bisa diakses MESKIPUN TRIAL SUDAH HABIS
Route::middleware(['auth', 'check.single.session'])->group(function () {
    // Halaman billing & profile (bisa diakses meski trial habis)
    Route::get('/billing', Billing::class)->name('billing');
    Route::get('/profile', Profile::class)->name('profile');
    // routes/web.php - tambahkan sementara di ATAS
    Route::get('/test-subscription-route', [PremiumController::class, 'subscription']);
    // Premium & Payment routes
    // routes/web.php
    Route::post('/payment/subscribe', [PaymentController::class, 'createSubscription'])->name('payment.subscribe');
    Route::get('/payment/finish', [PaymentController::class, 'paymentFinish'])->name('payment.finish');
    Route::get('/payment/cancel', [PaymentController::class, 'paymentCancel'])->name('payment.cancel');
    Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');
    Route::post('/payment/check-status', [PaymentController::class, 'checkPaymentStatus'])->name('payment.check-status');
    Route::get('/payment/history', [PaymentController::class, 'subscriptionHistory'])->name('payment.history');
    Route::get('/payment/validate-config', [PaymentController::class, 'validateConfiguration'])->name('payment.validate-config');
    // ✅ PASTIKAN INI ADA:
    Route::get('/upgrade', [PremiumController::class, 'subscription'])->name('subscription');
    Route::post('/subscription/update-profile', [PremiumController::class, 'updateProfile'])->name('subscription.update-profile');
   
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Routes yang HANYA bisa diakses jika TRIAL AKTIF atau PREMIUM
Route::middleware(['auth', 'check.single.session', 'check.trial'])->group(function () {
    // Dashboard & Main Features
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Tables & Examples
    Route::get('/tables', Tables::class)->name('tables');
    Route::get('/static-sign-in', StaticSignIn::class)->name('sign-in');
    Route::get('/static-sign-up', StaticSignUp::class)->name('static-sign-up');
    Route::get('/rtl', Rtl::class)->name('rtl');
    Route::get('/virtual-reality', VirtualReality::class)->name('virtual-reality');
    Route::get('/user-profile', UserProfile::class)->name('user-profile');
    Route::get('/user-management', UserManagement::class)->name('user-management');
    
    // Sector Analysis
    Route::get('/sector', [Sector2Controller::class, 'index'])->name('sector.index');
    
    // Signals Management
    Route::get('/signals', [SignalController::class, 'index'])->name('signals.index');
    Route::get('/signals/{symbol}', [SignalController::class, 'show'])->name('signals.show');
    Route::post('/signals/refresh', [SignalController::class, 'refresh'])->name('signals.refresh');
    
    // Performance Tracking
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
    Route::get('/performance/{id}', [PerformanceController::class, 'show'])->name('performance.show');
    Route::post('/performance/refresh', [PerformanceController::class, 'refresh'])->name('performance.refresh');
    Route::post('/performance/cleanup', [PerformanceController::class, 'cleanup'])->name('performance.cleanup');
    
    // Documentation
    Route::get('/documentation', function () {
        return view('documentation');
    })->name('documentation');

    // Market Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/market', [MarketDashboardController::class, 'index'])->name('dashboard.market');
        Route::get('/market/regime/{regimeType}', [MarketDashboardController::class, 'regimeDetail'])->name('dashboard.regime-detail');
        Route::get('/market/symbol/{symbol}', [MarketDashboardController::class, 'symbolDetail'])->name('dashboard.symbol-detail');
        Route::post('/market/alert/{alert}/read', [MarketDashboardController::class, 'markAlertRead'])->name('dashboard.alert-read');
        Route::post('/market/event/{event}/read', [MarketDashboardController::class, 'markEventRead'])->name('dashboard.event-read');
    });

    // AI Signals & Analysis
    Route::get('/signals/{id}', [AiSignalController::class, 'show'])->name('ai.signals.show');
    
    // Portfolio Management
    Route::get('/portfolio', PortfolioDashboard::class)->name('portfolio');
    Route::post('/positions/{position}/close', [PositionController::class, 'close'])->name('positions.close');
    Route::post('/portfolio/close-all', [PortfolioController::class, 'closeAll'])->name('portfolio.close-all');
    
    // Smart Signals & AI Decisions
    Route::get('/navbar/ai-decisions', [NavbarController::class, 'getAIDecisionsRunningText'])->name('navbar.ai-decisions');
    Route::get('/smart-signals', SmartSignals::class)->name('smart-signals');
    
    // AI Decisions Routes
    Route::get('/ai-decisions/{id}', function ($id) {
        $decision = App\Models\AiDecision::findOrFail($id);
        return view('signals.show', compact('decision'));
    })->name('ai-decisions.show');

    // Fallback routes untuk compatibility
    Route::get('/signals/fallback/{symbol}', [SignalController::class, 'show'])->name('signals.fallback');
    //setelah vps
    //trading real
    Route::get('/real-trading', RealTradingPage::class)->name('real-trading');
});

// ==================== WEBHOOK ROUTES (NO AUTH/MIDDLEWARE) ====================
Route::post('/webhook/midtrans', [PaymentController::class, 'handleWebhook'])->name('webhook.midtrans');

// ==================== CATCH-ALL FOR UNDEFINED ROUTES ====================
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});