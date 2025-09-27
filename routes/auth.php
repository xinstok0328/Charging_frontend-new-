<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExternalAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController; // 新增
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\MapController;
// use App\Http\Controllers\UserController; // 如果需要的話取消註解

// ------------ 未登入可訪問 ------------
Route::middleware('guest')->group(function () {
    // 顯示登入頁（對應 resources/views/auth/login.blade.php）
    Route::get('/login', [ExternalAuthController::class, 'showLoginForm'])->name('login'); // 添加名稱

    // 登入相關路由
    Route::post('/auth/login', [ExternalAuthController::class, 'login'])->name('auth.login');
    // Route::post('login', [AuthenticatedSessionController::class, 'store']); // 如果不需要可以註解掉
    Route::get('/auth/login', fn () => response('Use POST /auth/login', 405));

    // 註冊相關路由
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // 密碼重設
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');

    // 首頁
    Route::get('/', function () { return view('welcome'); });

    // 地圖相關路由
    Route::get('/map', [MapController::class, 'index']);
    Route::get('/map/markers', [MapController::class, 'markers']);

    // 驗證狀態檢查
    Route::get('/auth/status', [ExternalAuthController::class, 'checkAuthStatus'])->name('auth.status');

    Route::get('/user/info', [ExternalAuthController::class, 'userInfo'])->name('user.info');
});

// ------------ 已登入可訪問 ------------
Route::middleware('auth')->group(function () {
    // Email 驗證流程
    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // 密碼相關
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // 用戶資訊 - 移除重複定義
   

    
    Route::get('/info', function () { return view('info'); });
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    // 更新密碼
    Route::post('/user/update_pwd', [ExternalAuthController::class, 'updatePassword'])->name('user.update_pwd');
    
    // 登出 - 移到這裡因為需要驗證
    Route::post('/logout', [ExternalAuthController::class, 'logout'])->name('logout');


        // 診斷路由 - 放在 auth.php 檔案的最後面，在最後的 }); 之前
        Route::get('/debug/session', function() {
            return response()->json([
                'session_driver' => config('session.driver'),
                'session_id' => Session::getId(),
                'session_started' => Session::isStarted(),
                'user_authenticated' => Session::get('user_authenticated'),
                'has_auth_token' => !empty(Session::get('auth_token')),
                'auth_token_length' => Session::get('auth_token') ? strlen(Session::get('auth_token')) : 0,
                'user_data' => Session::get('user_data'),
                'all_session_keys' => array_keys(Session::all()),
                'session_file_path' => storage_path('framework/sessions'),
                'session_lifetime' => config('session.lifetime'),
            ]);
        });

        Route::get('/debug/auth-status', function() {
            return response()->json([
                'authenticated' => Session::get('user_authenticated', false),
                'has_token' => !empty(Session::get('auth_token')),
                'user_account' => Session::get('user_account'),
                'session_id' => Session::getId(),
                'timestamp' => now()->toISOString()
            ]);
        });



});

