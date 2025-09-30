<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ExternalAuthController extends Controller
{
    // 顯示登入頁面
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 別名方法，避免路由錯誤
    public function showLogin()
    {
        return $this->showLoginForm();
    }

    // 處理登入請求
    public function login(Request $request)
    {
        // 驗證表單資料
        $credentials = $request->validate([
            'account' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
        $endpoint = rtrim($base, '/') . '/auth/login';

        try {
            // 記錄請求資訊
            Log::info('Login API Request', [
                'endpoint' => $endpoint,
                'account' => $credentials['account']
            ]);

            // 呼叫後端 API 進行登入驗證
            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'account' => $credentials['account'],
                    'password' => $credentials['password'],
                ]);

            // 記錄 API 回應以供除錯
            Log::info('Login API Response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json()
            ]);

        } catch (\Throwable $e) {
            Log::error('External login HTTP exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '登入服務暫時無法使用',
                ], 503);
            }

            return back()
                ->withErrors(['login_error' => '登入服務暫時無法使用，請稍後再試'])
                ->withInput($request->except('password'));
        }

        // 檢查 HTTP 回應狀態
        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? '登入服務暫時無法使用';

            Log::warning('Login API failed', [
                'status' => $response->status(),
                'response' => $errorData
            ]);

            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], $response->status());
            }

            return back()
                ->withErrors(['login_error' => $errorMessage])
                ->withInput($request->except('password'));
        }

        $loginData = $response->json();

        // 根據實際的 API 回應格式檢查登入結果
        if (isset($loginData['success']) && $loginData['success'] === true && isset($loginData['data'])) {
            // 提取 token 和用戶資料
            $userData = $loginData['data'];
            $token = $userData['token'] ?? null;

            if (!$token) {
                Log::warning('No token in successful login response', ['response' => $loginData]);
                
                if ($request->wantsJson() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => '登入回應中缺少認證令牌',
                    ], 500);
                }

                return back()
                    ->withErrors(['login_error' => '登入回應格式異常'])
                    ->withInput($request->except('password'));
            }

            // 儲存登入狀態和資料
            Session::put('user_authenticated', true);
            Session::put('user_account', $credentials['account']);
            Session::put('auth_token', $token);
            Session::put('user_data', $userData);
            Session::put('auth_token', $token);
            
            // 儲存完整的用戶資料
            Session::put('user_data', [
                'account' => $userData['account'] ?? $credentials['account'],
                'name' => $userData['name'] ?? null,
                'nick_name' => $userData['nick_name'] ?? null,
                'role_id' => $userData['role_id'] ?? null,
                'role_name' => $userData['role_name'] ?? null,
                'role_code' => $userData['role_code'] ?? null,
            ]);

            Log::info('Login successful', [
                'account' => $credentials['account'],
                'token_length' => strlen($token),
                'user_data' => Session::get('user_data')
            ]);

            // 根據請求類型決定回應方式
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json($loginData, 200);
            } else {
                $redirectTo = $request->input('redirect_to', '/map');
                return redirect()->to($redirectTo)
                    ->with('success', '登入成功！歡迎使用充電站管理系統');
            }

        } else {
            // 登入失敗
            $errorMessage = $loginData['message'] ?? '帳號或密碼錯誤';
            
            Log::warning('Login failed', [
                'account' => $credentials['account'],
                'response' => $loginData
            ]);
            
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 401);
            }

            return back()
                ->withErrors(['login_error' => $errorMessage])
                ->withInput($request->except('password'));
        }
    }


    /**
 * 獲取用戶資訊方法 - 直接串接後端 API
 */
public function getUserInfo(Request $request): JsonResponse
{
    // 檢查用戶是否已登入（保留基本認證檢查）
    $isAuthenticated = Session::get('user_authenticated', false);
    $hasToken = !empty(Session::get('auth_token'));
    
    if (!$isAuthenticated && !$hasToken) {
        Log::warning('Unauthorized getUserInfo attempt', [
            'session_id' => Session::getId(),
            'user_authenticated' => $isAuthenticated,
            'has_token' => $hasToken
        ]);
        
        return response()->json([
            'success' => false,
            'message' => '請先登入',
        ], 401);
    }

    // 註解掉 Session 資料檢查，強制調用後端 API
    /*
    // 優先從 session 獲取用戶資料
    $userData = Session::get('user_data');
    
    // 如果 session 中有完整的用戶資料，直接返回
    if ($userData && !empty($userData['account'])) {
        Log::info('Returning user data from session', [
            'account' => $userData['account'],
            'session_id' => Session::getId()
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $userData
        ], 200);
    }
    */

    // 獲取認證 token
    $token = Session::get('auth_token');
    if (!$token) {
        Log::warning('No auth token found for getUserInfo');
        
        return response()->json([
            'success' => false,
            'message' => '認證令牌無效，請重新登入',
        ], 401);
    }

    // 強制調用外部 API 獲取用戶資料
    Log::info('Forcing API call for user info', [
        'token_length' => strlen($token),
        'session_id' => Session::getId()
    ]);

    return $this->fetchUserInfoFromAPI($token, $request);
}



    /**
     * 新增：獲取用戶資訊方法 - 對應前端的 /user/info 路由
     */
    // public function getUserInfo(Request $request): JsonResponse
    // {


    //     // 檢查用戶是否已登入
    //     $isAuthenticated = Session::get('user_authenticated', false);
    // $hasToken = !empty(Session::get('auth_token'));

    //  Log::info('getUserInfo called with session data', [
    //     'session_id' => Session::getId(),
    //     'user_authenticated' => Session::get('user_authenticated'),
    //     'has_auth_token' => !empty(Session::get('auth_token')),
    //     'session_all' => Session::all()
    // ]);
    
    // // 如果沒有認證標誌但有 token，也允許通過
    // if (!$isAuthenticated && !$hasToken) {
    //     Log::warning('Unauthorized getUserInfo attempt', [
    //         'session_id' => Session::getId(),
    //         'user_authenticated' => $isAuthenticated,
    //         'has_token' => $hasToken
    //     ]);
        
    //     return response()->json([
    //         'success' => false,
    //         'message' => '請先登入',
    //     ], 401);
    // }

    //     // 優先從 session 獲取用戶資料
    //     $userData = Session::get('user_data');
        
    //     // 如果 session 中有完整的用戶資料，直接返回
    //     if ($userData && !empty($userData['account'])) {
    //         Log::info('Returning user data from session', [
    //             'account' => $userData['account'],
    //             'session_id' => Session::getId()
    //         ]);
            
    //         return response()->json([
    //             'success' => true,
    //             'data' => $userData
    //         ], 200);
    //     }

    //     // 如果 session 中沒有用戶資料，嘗試從外部 API 獲取
    //     $token = Session::get('auth_token');
    //     if (!$token) {
    //         Log::warning('No auth token found for getUserInfo');
            
    //         return response()->json([
    //             'success' => false,
    //             'message' => '認證令牌無效，請重新登入',
    //         ], 401);
    //     }

    //     // 呼叫外部 API 獲取用戶資料
    //     return $this->fetchUserInfoFromAPI($token, $request);
    // }



    
    /**
 * 從外部 API 獲取用戶資訊
 */
private function fetchUserInfoFromAPI(string $token, Request $request): JsonResponse
{
    // 使用正確的環境變數 - 與您的 .env 一致
    $base = config('services.backend.base_url', env('EXT_API_BASE', 'http://120.110.115.126:18081'));
    $endpoint = rtrim($base, '/') . '/user/info';

    try {
        Log::info('Fetching user info from external API', [
            'endpoint' => $endpoint,
            'token_length' => strlen($token),
            'session_id' => Session::getId()
        ]);

        $response = Http::timeout(15)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            ->get($endpoint);

        Log::info('External API user info response', [
            'status' => $response->status(),
            'success' => $response->successful(),
            'body' => $response->json()
        ]);

        // 根據 API 文檔處理不同的回應狀態
        switch ($response->status()) {
            case 200:
                // 成功回應
                $apiData = $response->json();
                
                if (isset($apiData['success']) && $apiData['success'] === true && isset($apiData['data'])) {
                    // 處理用戶資料，確保格式一致
                    $userData = $apiData['data'];
                    
                    // 標準化用戶資料格式
                    $standardizedUserData = [
                        'id' => $userData['id'] ?? null,
                        'account' => $userData['account'] ?? Session::get('user_account'),
                        'name' => $userData['name'] ?? null,
                        'email' => $userData['email'] ?? null,
                        'phone' => $userData['phone'] ?? null,
                        'role_name' => $userData['role_name'] ?? null,
                        'role_code' => $userData['role_code'] ?? null,
                        'create_time' => $userData['create_time'] ?? null,
                        'modify_time' => $userData['modify_time'] ?? null,
                        'user_img_responses' => $userData['user_img_responses'] ?? []
                    ];
                    
                    // 更新 session 中的用戶資料
                    Session::put('user_data', $standardizedUserData);
                    
                    Log::info('Successfully fetched and updated user data', [
                        'user_id' => $standardizedUserData['id'],
                        'account' => $standardizedUserData['account']
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'code' => 0,
                        'message' => '獲取用戶資料成功',
                        'data' => $standardizedUserData
                    ], 200);
                    
                } else {
                    Log::warning('API returned success=false or missing data', [
                        'response' => $apiData
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'code' => 1,
                        'message' => '用戶資料格式錯誤',
                        'data' => []
                    ], 500);
                }
                break;
                
            case 400:
                // Bad Request
                Log::warning('Bad request to user info API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'code' => 1,
                    'message' => '請求格式錯誤',
                    'data' => []
                ], 400);
                
            case 401:
                // Unauthorized - 認證失敗
                Log::warning('Unauthorized access to user info API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'token_length' => strlen($token)
                ]);
                
                // 清除過期的認證資訊
                $this->clearAuthSession();
                
                return response()->json([
                    'success' => false,
                    'code' => 1,
                    'message' => '認證失敗，請重新登入',
                    'data' => []
                ], 401);
                
            case 403:
                // Forbidden - 沒有權限
                Log::warning('Forbidden access to user info API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'code' => 1,
                    'message' => '沒有權限訪問用戶資料',
                    'data' => []
                ], 403);
                
            case 404:
                // Not Found - 用戶不存在
                Log::warning('User not found in API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'code' => 1,
                    'message' => '用戶資料不存在',
                    'data' => []
                ], 404);
                
            case 500:
                // Internal Server Error
                Log::error('Internal server error from user info API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'code' => 1,
                    'message' => '伺服器內部錯誤，請稍後再試',
                    'data' => []
                ], 500);
                
            default:
                // 其他未預期的狀態碼
                Log::warning('Unexpected status code from user info API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'code' => 1,
                    'message' => '獲取用戶資料時發生未預期的錯誤',
                    'data' => []
                ], $response->status());
        }

    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        // 網路連線錯誤
        Log::error('Connection error to user info API', [
            'endpoint' => $endpoint,
            'error' => $e->getMessage(),
            'error_type' => 'ConnectionException'
        ]);

        return response()->json([
            'success' => false,
            'code' => 1,
            'message' => '無法連接到伺服器，請檢查網路連線',
            'data' => []
        ], 503);
        
    } catch (\Illuminate\Http\Client\RequestException $e) {
        // HTTP 請求錯誤
        Log::error('Request error to user info API', [
            'endpoint' => $endpoint,
            'error' => $e->getMessage(),
            'error_type' => 'RequestException'
        ]);

        return response()->json([
            'success' => false,
            'code' => 1,
            'message' => '請求用戶資料時發生錯誤',
            'data' => []
        ], 503);
        
    } catch (\Throwable $e) {
        // 其他所有錯誤
        Log::error('Unexpected error in user info API call', [
            'endpoint' => $endpoint,
            'error' => $e->getMessage(),
            'error_type' => get_class($e),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'code' => 1,
            'message' => '查詢服務暫時不可用',
            'data' => []
        ], 503);
    }
}

    /**
     * 原有的 userInfo 方法 - 保持兼容性
     */
    public function userInfo(Request $request): JsonResponse
    {
        return $this->getUserInfo($request);
    }

    // 登出功能
    public function logout(Request $request): RedirectResponse
    {
        $this->clearAuthSession();
        
        return redirect('/login')
            ->with('success', '已成功登出');
    }

    /**
     * 清除認證 session 的輔助方法
     */
    private function clearAuthSession(): void
    {
        Session::forget([
            'user_authenticated', 
            'user_account', 
            'auth_token', 
            'user_data'
        ]);
        
        // 完全清除 session
        Session::flush();
    }

    /**
     * 檢查認證狀態的輔助方法
     */
    public function checkAuthStatus(): JsonResponse
    {
        $isAuthenticated = Session::get('user_authenticated', false);
        $hasToken = !empty(Session::get('auth_token'));
        $userData = Session::get('user_data');

        return response()->json([
            'authenticated' => $isAuthenticated,
            'has_token' => $hasToken,
            'user' => $userData,
            'session_id' => Session::getId()
        ]);
    }

    /**
     * 回傳目前使用者的 Session token（需已登入）
     */
    public function getSessionToken(): JsonResponse
    {
        $token = Session::get('auth_token');

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => '未找到 token，請先登入',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ], 200);
    }

    /**
     * 測試後端連線
     */
    public function testConnection(): JsonResponse
    {
        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
        
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(10)->get($base);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            return response()->json([
                'success' => true,
                'endpoint' => $base,
                'status_code' => $response->status(),
                'response_time_ms' => $responseTime,
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500),
                'test_time' => now()->toISOString()
            ]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'endpoint' => $base,
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'test_time' => now()->toISOString()
            ], 503);
        }
    }

    /**
     * 測試登入 API 端點
     */
    public function testLoginAPI(): JsonResponse
    {
        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
        $endpoint = rtrim($base, '/') . '/auth/login';
        
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, []);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            return response()->json([
                'success' => true,
                'endpoint' => $endpoint,
                'status_code' => $response->status(),
                'response_time_ms' => $responseTime,
                'response_body' => $response->json(),
                'test_time' => now()->toISOString(),
                'note' => 'This is a test without credentials'
            ]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'endpoint' => $endpoint,
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'test_time' => now()->toISOString()
            ], 503);
        }
    }

    /**
     * 更新使用者密碼
     */
    public function updatePassword(Request $request): JsonResponse
    {
        // 驗證請求參數
        $request->validate([
            'oldPassword' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // 檢查使用者是否已登入
        if (!Session::get('user_authenticated', false)) {
            Log::warning('Unauthorized password update attempt', [
                'session_id' => Session::getId(),
                'user_authenticated' => Session::get('user_authenticated', false)
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '請先登入',
            ], 401);
        }

        // 獲取認證 token
        $token = Session::get('auth_token');
        if (!$token) {
            Log::warning('No auth token found for password update');
            
            return response()->json([
                'success' => false,
                'message' => '認證令牌無效，請重新登入',
            ], 401);
        }

        // 設定外部 API 端點
        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
        $endpoint = rtrim($base, '/') . '/user/update_pwd';

        try {
            Log::info('Password update API request', [
                'endpoint' => $endpoint,
                'user_account' => Session::get('user_account'),
                'token_length' => strlen($token)
            ]);

            // 調用外部 API 進行密碼更新
            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->post($endpoint, [
                    'oldPassword' => $request->input('oldPassword'),
                    'password' => $request->input('password'),
                ]);

            Log::info('Password update API response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->json()
            ]);

        } catch (\Throwable $e) {
            Log::error('External password update HTTP exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '密碼更新服務暫時無法使用',
            ], 503);
        }

        // 檢查 HTTP 響應狀態
        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? '密碼更新失敗';

            Log::warning('Password update API failed', [
                'status' => $response->status(),
                'response' => $errorData
            ]);

            // 根據不同的 HTTP 狀態碼返回適當的錯誤訊息
            $statusCode = $response->status();
            if ($statusCode === 401) {
                $errorMessage = '舊密碼不正確或認證已過期';
                $this->clearAuthSession(); // 清除過期的認證
            } elseif ($statusCode === 422) {
                $errorMessage = '密碼格式不符合要求';
            } elseif ($statusCode === 400) {
                $errorMessage = '請求參數錯誤';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], $statusCode === 401 ? 401 : 400);
        }

        $updateData = $response->json();

        // 檢查 API 響應格式
        if (isset($updateData['success']) && $updateData['success'] === true) {
            Log::info('Password update successful', [
                'user_account' => Session::get('user_account'),
                'response' => $updateData
            ]);

            return response()->json([
                'success' => true,
                'message' => '密碼更新成功',
                'data' => $updateData['data'] ?? null
            ], 200);

        } else {
            // 更新失敗
            $errorMessage = $updateData['message'] ?? '密碼更新失敗';
            
            Log::warning('Password update failed', [
                'user_account' => Session::get('user_account'),
                'response' => $updateData
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 400);
        }
    }

    /**
     * 更新會員資料
     */
    public function updateProfile(Request $request): JsonResponse
    {
        // 驗證請求參數（僅允許這四個欄位）
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'file_id' => 'nullable|integer',
        ]);

        // 檢查使用者是否已登入
        if (!Session::get('user_authenticated', false)) {
            Log::warning('Unauthorized profile update attempt', [
                'session_id' => Session::getId(),
                'user_authenticated' => Session::get('user_authenticated', false)
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '請先登入',
            ], 401);
        }

        // 獲取認證 token
        $token = Session::get('auth_token');
        if (!$token) {
            Log::warning('No auth token found for profile update');
            
            return response()->json([
                'success' => false,
                'message' => '認證令牌無效，請重新登入',
            ], 401);
        }

        // 設定外部 API 端點
        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));

        // 取得使用者 ID（先從 session，沒有就打 /user/info 補齊）
        $userData = Session::get('user_data', []);
        $userId = $userData['id'] ?? null;
        if (!$userId) {
            try {
                $infoEndpoint = rtrim($base, '/') . '/user/info';
                $infoResp = Http::timeout(10)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ])->get($infoEndpoint);
                if ($infoResp->successful()) {
                    $infoJson = $infoResp->json();
                    $fromApi = $infoJson['data'] ?? [];
                    $userId = $fromApi['id'] ?? null;
                    if (!empty($fromApi)) {
                        Session::put('user_data', array_merge($userData, $fromApi));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Fetch user info before update failed', ['error' => $e->getMessage()]);
            }
        }

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => '找不到使用者 ID，請重新整理或重新登入後再試',
            ], 400);
        }

        // 依文件：PUT /user/update/{id}
        $endpoint = rtrim($base, '/') . '/user/update/' . urlencode((string) $userId);

        try {
            Log::info('Profile update API request', [
                'endpoint' => $endpoint,
                'user_account' => Session::get('user_account'),
                'token_length' => strlen($token),
                'update_data' => $request->only(['name', 'email', 'phone', 'file_id'])
            ]);

            // 組裝僅包含有效值的負載
            $payload = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'file_id' => (int) ($request->input('file_id', 0)),
                // 同時提供 camelCase 與 snake_case，避免後端鍵名不同導致 400
                'fileId' => (int) ($request->input('file_id', $request->input('fileId', 0))),
            ];

            // 調用外部 API 進行會員資料更新
            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->put($endpoint, $payload);

            Log::info('Profile update API response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->json()
            ]);

        } catch (\Throwable $e) {
            Log::error('External profile update HTTP exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '會員資料更新服務暫時無法使用',
            ], 503);
        }

        // 檢查 HTTP 響應狀態
        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? '會員資料更新失敗';

            Log::warning('Profile update API failed', [
                'status' => $response->status(),
                'response' => $errorData
            ]);

            // 根據不同的 HTTP 狀態碼返回適當的錯誤訊息
            $statusCode = $response->status();
            if ($statusCode === 401) {
                $errorMessage = '認證已過期，請重新登入';
                $this->clearAuthSession(); // 清除過期的認證
            } elseif ($statusCode === 422) {
                $errorMessage = '資料格式不符合要求';
            } elseif ($statusCode === 400) {
                $errorMessage = '請求參數錯誤';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'backend' => $errorData,
            ], $statusCode === 401 ? 401 : 400);
        }

        $updateData = $response->json();

        // 檢查 API 響應格式
        if (isset($updateData['success']) && $updateData['success'] === true) {
            Log::info('Profile update successful', [
                'user_account' => Session::get('user_account'),
                'response' => $updateData
            ]);

            return response()->json([
                'success' => true,
                'message' => '會員資料更新成功',
                'data' => $updateData['data'] ?? null
            ], 200);

        } else {
            // 更新失敗
            $errorMessage = $updateData['message'] ?? '會員資料更新失敗';
            
            Log::warning('Profile update failed', [
                'user_account' => Session::get('user_account'),
                'response' => $updateData
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'backend' => $updateData,
            ], 400);
        }
    }

    /**
     * 安全的 JSON 解析輔助方法
     */
    private function safeJson($data)
    {
        if (is_array($data) || is_object($data)) {
            return $data;
        }
        
        try {
            return json_decode($data, true);
        } catch (\Throwable $e) {
            return ['error' => 'Invalid JSON format'];
        }
    }
}