<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 其餘 Breeze 預設
Route::middleware('auth')->group(function () {
    // ... 你的 profile 等路由（保留原本）
});

require __DIR__.'/auth.php';
