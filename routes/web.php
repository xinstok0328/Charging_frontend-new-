<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 需要登入才可用的路由
Route::middleware('auth')->group(function () {
    Route::put('/user/update_profile', [ProfileController::class, 'update'])
        ->name('user.update');
});

require __DIR__.'/auth.php';
