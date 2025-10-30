<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return view('welcome');
});

// 地圖頁（付款完成回跳目標頁）
Route::get('/map', function () {
    return view('map');
})->name('map');

// 藍新金流前端回傳頁（OrderResultURL/ClientBackURL 可設定到此）
// 收到後立即導回地圖頁
Route::match(['GET', 'POST'], '/payment/result', function () {
    // 可在此驗簽/記錄必要資訊後再導回
    return redirect()->route('map');
})->name('payment.result');

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
    // 調用外部 API 開始充電
    $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
    $endpoint = rtrim($base, '/') . '/user/purchase/start';
    
    try {
        $token = session('auth_token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'code' => 1,
                'message' => '認證令牌無效，請重新登入',
                'data' => null
            ], 401);
        }
        
        // 獲取請求參數
        $requestData = [
            'pile_id' => request('pile_id') ?? request('pileId'),
            'start_time' => request('start_time') ?? request('startTime'),
            'end_time' => request('end_time') ?? request('endTime')
        ];
        
        $response = Http::timeout(15)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->post($endpoint, $requestData);
        
        if ($response->successful()) {
            $data = $response->json();
            
            // 如果外部 API 回應成功，保存 charging_bill_id 和 session_id 到 session
            if (isset($data['data'])) {
                $apiData = $data['data'];
                $chargingBillId = $apiData['charging_bill_id'] ?? null;
                $sessionId = $apiData['session_id'] ?? null;
                
                // 保存到 session
                if ($chargingBillId) {
                    session(['charging_bill_id' => $chargingBillId]);
                    session(['charging_session_id' => $sessionId ?? $chargingBillId]);
                }
                
                // 設置充電狀態
                session(['has_active_reservation' => true]);
                session(['reservation_status' => 'IN_PROGRESS']);
                session(['reservation_id' => $chargingBillId ?? $sessionId]);
                
                if (isset($apiData['start_time'])) {
                    session(['reservation_start_time' => $apiData['start_time']]);
                }
                if (isset($apiData['end_time'])) {
                    session(['reservation_end_time' => $apiData['end_time']]);
                }
                if (isset($apiData['pile_response']['id'])) {
                    session(['reservation_pile_id' => $apiData['pile_response']['id']]);
                }
            }
            
            // 返回外部 API 的回應
            return response()->json($data, $response->status());
        } else {
            // 如果外部 API 失敗，返回錯誤
            return response()->json([
                'success' => false,
                'code' => $response->status(),
                'message' => $response->json()['message'] ?? '開始充電失敗',
                'data' => null
            ], $response->status());
        }
    } catch (\Throwable $e) {
        Log::error('開始充電 API 調用失敗', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'code' => 500,
            'message' => '開始充電時發生錯誤：' . $e->getMessage(),
            'data' => null
        ], 500);
    }
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
    // 優先使用 charging_bill_id
    $chargingBillId = null;
    
    // 如果有進行中的充電，優先使用開始充電時保存的 charging_bill_id
    $hasActiveReservation = session('has_active_reservation', false);
    $reservationStatus = session('reservation_status');
    
    if ($hasActiveReservation && $reservationStatus === 'IN_PROGRESS') {
        // 優先使用開始充電時保存的 charging_bill_id
        $chargingBillId = session('charging_bill_id')
            ?? session('reservation_id')
            ?? session('charging_session_id'); // 補充：開始充電時也有保存 charging_session_id
    }
    
    // 如果沒有進行中的充電或沒有 charging_bill_id，使用登入時後端回傳的 session_id 作為備用
    if (!$chargingBillId || $chargingBillId === 0 || $chargingBillId === '0') {
        $userData = session('user_data', []);
        // 登入時的 session_id 可能就是 charging_bill_id
        $chargingBillId = $userData['session_id'] ?? null;
    }
    
    // 調試日誌
    Log::info('statusIng - 嘗試獲取 charging_bill_id', [
        'has_active_reservation' => $hasActiveReservation,
        'reservation_status' => $reservationStatus,
        'charging_bill_id' => session('charging_bill_id'),
        'reservation_id' => session('reservation_id'),
        'user_data_session_id' => $userData['session_id'] ?? null,
        'final_charging_bill_id' => $chargingBillId
    ]);
    
    // 如果還是沒有，嘗試從 request 參數獲取（向後兼容）
    if (!$chargingBillId || $chargingBillId === 0 || $chargingBillId === '0') {
        $chargingBillId = request('charging_bill_id')
            ?? request('session_id')
            ?? request('sessionId'); // 一些前端以 sessionId 傳遞
    }
    
    // 如果還是沒有 charging_bill_id，返回錯誤
    if (!$chargingBillId || $chargingBillId === 0 || $chargingBillId === '0') {
        Log::warning('statusIng - 找不到有效的 charging_bill_id');
        return response()->json([
            'success' => false,
            'code' => 1,
            'message' => '找不到充電會話 ID，請重新預約',
            'data' => null
        ], 400);
    }
    
    // 使用 charging_bill_id 作為 session_id 傳給外部 API（後端可能接受）
    $sessionId = $chargingBillId;
    
    // 調用外部 API 獲取真實充電狀態
    $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
    $endpoint = rtrim($base, '/') . '/user/purchase/status_ing';
    
    try {
        $token = session('auth_token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'code' => 1,
                'message' => '認證令牌無效，請重新登入',
                'data' => null
            ], 401);
        }
        
        $response = Http::timeout(15)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->get($endpoint, [
                'session_id' => $sessionId
            ]);
        
        if ($response->successful()) {
            $data = $response->json();
            
            // 確保回應中包含 session_id（如果外部 API 沒有提供）
            if (isset($data['data']) && !isset($data['data']['session_id'])) {
                $data['data']['session_id'] = $sessionId;
            }
            
            // 返回後端 API 的回應（已確保包含 session_id）
            return response()->json($data, $response->status());
        } else {
            // 如果外部 API 失敗，返回模擬數據
            return response()->json([
                'success' => true,
                'code' => 0,
                'message' => '獲取充電狀態成功',
                'data' => [
                    'session_id' => $sessionId,
                    'start_time' => now()->subMinutes(30)->toISOString(),
                    'amount' => 25, // 30分鐘的費用
                    'status' => 'IN_PROGRESS'
                ]
            ]);
        }
    } catch (\Throwable $e) {
        // 如果外部 API 調用失敗，返回模擬數據
        Log::warning('Failed to fetch charging status from external API', [
            'session_id' => $sessionId,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => '獲取充電狀態成功',
            'data' => [
                'session_id' => $sessionId,
                'start_time' => now()->subMinutes(30)->toISOString(),
                'amount' => 25,
                'status' => 'IN_PROGRESS'
            ]
        ]);
    }
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
