<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExternalAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\MapController;

// ------------ 未登入可訪問 ------------
Route::middleware('guest')->group(function () {
    // 顯示登入頁（對應 resources/views/auth/login.blade.php）
   Route::get('/login', [ExternalAuthController::class, 'showLoginForm']); 

    // 登入：你的前端會送到 route('auth.login') => POST /auth/login
    Route::post('/auth/login', [ExternalAuthController::class, 'login'])->name('auth.login');

     Route::get('/auth/login', fn () => response('Use POST /auth/login', 405));
     Route::post('/logout', [ExternalAuthController::class, 'logout'])->name('logout');

    // 其餘 Breeze 預設（如需要）
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');

    Route::get('/', function () { return view('welcome'); });

    // // 地圖相關路由
    // Route::get('/map', [MapController::class, 'map'])->name('map');

    // // API 路由 (也可以放在 routes/api.php)
    // Route::prefix('api')->group(function () {
    //     Route::get('/nearby', [MapController::class, 'nearby'])->name('api.nearby');
    //     Route::get('/map/test', [MapController::class, 'test'])->name('api.map.test');
    // });
    Route::get('/map', [MapController::class, 'index']);
    Route::get('/map/markers', [MapController::class, 'markers']);

Route::get('/auth/status', [ExternalAuthController::class, 'checkAuthStatus'])->name('auth.status');
});

// ------------ 已登入可訪問 ------------
Route::middleware('auth')->group(function () {
    // 可選：Email 驗證流程（如需要）
    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // 修改密碼（如需要）
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // 取得使用者資訊（會帶 token）
    Route::get('/user/info', [ExternalAuthController::class, 'userInfo'])->name('user.info');
    Route::get('/info', function () { return view('info');}); // 如果直接返回視圖
    Route::get('/user/info', [UserController::class, 'getUserInfo']);
  
    Route::get('/dashboard', function () {return view('dashboard');})->middleware(['auth','verified'])->name('dashboard');

    Route::post('/user/update_pwd', [ExternalAuthController::class, 'updatePassword'])
    ->middleware('auth')->name('user.update_pwd');
    
});

