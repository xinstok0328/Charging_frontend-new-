<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return view('welcome');
});

// 調試頁面
Route::get('/debug-login', function () {
    return view('debug-login');
});

// Email 生成器
Route::get('/email-generator', function () {
    return view('email-generator');
});

// Dashboard route is defined in routes/auth.php under custom.auth middleware

// 需要登入才可用的路由
Route::middleware('auth')->group(function () {
    Route::put('/user/update_profile', [ProfileController::class, 'update'])
        ->name('user.update');
});

require __DIR__.'/auth.php';
