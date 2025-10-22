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

// 充電相關路由（暫時不需要認證）
Route::post('/user/purchase/start', function () {
    // 模擬開始充電 API 回應
    // 設置 session 狀態為進行中
    session(['has_active_reservation' => true]);
    session(['reservation_status' => 'IN_PROGRESS']);
    session(['reservation_id' => rand(1000, 9999)]);
    session(['reservation_start_time' => now()->toISOString()]);
    session(['reservation_end_time' => now()->addHour()->toISOString()]);
    session(['reservation_pile_id' => request('pile_id', 1)]);
    
    return response()->json([
        'success' => true,
        'code' => 0,
        'message' => '充電已開始',
        'data' => [
            'session_id' => session('reservation_id'),
            'start_time' => session('reservation_start_time'),
            'end_time' => session('reservation_end_time'),
            'price_per_hour' => 50,
            'duration_min' => 60,
            'service_fee' => 10,
            'total_amount' => 0,
            'discount_amount' => 0,
            'final_amount' => 0
        ]
    ]);
});

Route::post('/user/purchase/end', function () {
    // 模擬完成充電 API 回應
    // 更新 session 狀態為完成
    session(['has_active_reservation' => false]);
    session(['reservation_status' => 'COMPLETED']);
    
    // 獲取請求參數 - 簡化版本，只接收 session_id
    $sessionId = request('session_id', 0);
    
    // 模擬從 session 或資料庫獲取充電會話數據
    $startTime = session('reservation_start_time', now()->subHour()->toISOString());
    $endTime = now()->toISOString();
    $pricePerHour = 50; // 預設每小時價格
    $durationMin = 60; // 預設充電時長
    $serviceFee = 10;   // 預設服務費
    $totalAmount = 50; // 預設總金額
    $discountAmount = 0; // 預設折扣
    $finalAmount = 50;   // 預設最終金額
    
    // 如果有 session_id，可以從中推導一些數據
    if ($sessionId > 0) {
        // 模擬根據 session_id 獲取實際數據
        $durationMin = rand(30, 120); // 隨機充電時長 30-120 分鐘
        $totalAmount = ($pricePerHour * $durationMin) / 60;
        $finalAmount = $totalAmount + $serviceFee - $discountAmount;
    }
    
    return response()->json([
        'success' => true,
        'code' => 0,
        'message' => '充電已完成',
        'data' => [
            'charging_bill_id' => rand(1000, 9999),
            'session_id' => (int)$sessionId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'price_per_hour' => (int)$pricePerHour,
            'duration_min' => (int)$durationMin,
            'service_fee' => (int)$serviceFee,
            'total_amount' => (float)$totalAmount,
            'discount_amount' => (float)$discountAmount,
            'final_amount' => (float)$finalAmount,
            'payment_status' => 'UNPAID',
            'pile_response' => [
                'id' => session('reservation_pile_id', 1),
                'model' => 'AC Charger Model A',
                'connector_type' => 'Type 2',
                'max_kw' => 22,
                'firmware_version' => 'v1.2.3',
                'location_address' => '台北市信義區信義路五段7號',
                'lat' => 25.0330,
                'lng' => 121.5654
            ],
            'payment_transaction_responses' => [
                [
                    'payment_method' => 'CREDIT_CARD',
                    'provider' => 'Stripe',
                    'provider_transaction_id' => 'txn_' . rand(100000, 999999),
                    'amount' => (float)$finalAmount,
                    'currency' => 'TWD',
                    'status' => 'PENDING',
                    'message' => 'Payment processing',
                    'request_time' => now()->toISOString(),
                    'completed_time' => null,
                    'meta' => '{"source": "mobile_app"}'
                ]
            ]
        ]
    ]);
});

Route::get('/user/purchase/top', function () {
    // 模擬預約狀態檢查 API
    // 檢查是否有進行中的預約
    $hasActiveReservation = session('has_active_reservation', false);
    $reservationStatus = session('reservation_status', 'NONE');
    
    if ($hasActiveReservation && $reservationStatus === 'IN_PROGRESS') {
        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => '有進行中的預約',
            'data' => [
                'id' => session('reservation_id', 1),
                'status' => $reservationStatus,
                'start_time' => session('reservation_start_time', now()->subHour()->toISOString()),
                'end_time' => session('reservation_end_time', now()->toISOString()),
                'pile_id' => session('reservation_pile_id', 1)
            ]
        ]);
    } else {
        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => '目前無預約',
            'data' => null
        ]);
    }
});

Route::get('/user/purchase/statusIng', function () {
    $sessionId = request('session_id');
    
    // 根據 Swagger API 文檔格式返回
    return response()->json([
        'success' => true,
        'code' => 0,
        'message' => 'string',
        'data' => [
            'start_time' => now()->subMinutes(30)->toISOString(),
            'amount' => 25, // 30分鐘的費用
            'status' => 'RESERVED' // API 只返回 RESERVED 狀態
        ]
    ]);
});

Route::post('/user/purchase/reserve', function () {
    // 模擬預約 API
    $pileId = request('pile_id', request('pileId', 1));
    $startTime = request('start_time', request('startTime', now()->toISOString()));
    $endTime = request('end_time', request('endTime', now()->addHour()->toISOString()));
    
    // 設置 session 狀態為已預約
    session(['has_active_reservation' => true]);
    session(['reservation_status' => 'RESERVED']);
    session(['reservation_id' => rand(1000, 9999)]);
    session(['reservation_start_time' => $startTime]);
    session(['reservation_end_time' => $endTime]);
    session(['reservation_pile_id' => $pileId]);
    
    return response()->json([
        'success' => true,
        'code' => 0,
        'message' => '預約成功',
        'data' => [
            'id' => session('reservation_id'),
            'pile_id' => $pileId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'RESERVED'
        ]
    ]);
});

// 需要登入才可用的路由
Route::middleware('auth')->group(function () {
    Route::put('/user/update_profile', [ProfileController::class, 'update'])
        ->name('user.update');
});

require __DIR__.'/auth.php';
