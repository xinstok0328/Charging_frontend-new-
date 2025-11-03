<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TariffController extends Controller
{
    /**
     * Get charging tariff information
     * user_id 和 user_tier_id 從 session 自動獲取
     * pile_id 從請求參數獲取
     */
    public function getTariff(Request $request): JsonResponse
    {
        try {
            // 只驗證 pile_id
            $validator = Validator::make($request->all(), [
                'pile_id' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'code' => 422,
                    'message' => 'Parameter validation failed: ' . $validator->errors()->first(),
                    'data' => null
                ], 422);
            }

            // 從 session 獲取用戶資訊
            $userData = Session::get('user_data');
            $userId = $userData['id'] ?? null;
            $userTierId = $userData['user_tier_id'] ?? $userId; // 如果沒有 user_tier_id，使用 user_id

            // Fallback：若缺少 user_id 但有 auth_token，呼叫外部 /user/info 補齊並寫回 Session
            if (!$userId) {
                $token = Session::get('auth_token');
                if (!empty($token)) {
                    try {
                        $base = config('services.backend.base_url', env('EXT_API_BASE', 'http://120.110.115.126:18081'));
                        $endpoint = rtrim($base, '/') . '/user/info';

                        Log::info('TariffController fallback fetching user info', [
                            'endpoint' => $endpoint,
                            'token_length' => strlen($token),
                            'session_id' => Session::getId()
                        ]);

                        $resp = Http::timeout(10)
                            ->withHeaders([
                                'Accept' => 'application/json',
                                'Content-Type' => 'application/json',
                                'Authorization' => 'Bearer ' . $token,
                            ])->get($endpoint);

                        if ($resp->successful()) {
                            $json = $resp->json();
                            $apiUser = $json['data'] ?? [];
                            if (!empty($apiUser)) {
                                $standardized = [
                                    'id' => $apiUser['id'] ?? null,
                                    'account' => $apiUser['account'] ?? ($userData['account'] ?? null),
                                    'name' => $apiUser['name'] ?? ($userData['name'] ?? null),
                                    'email' => $apiUser['email'] ?? ($userData['email'] ?? null),
                                    'phone' => $apiUser['phone'] ?? ($userData['phone'] ?? null),
                                    'role_name' => $apiUser['role_name'] ?? ($userData['role_name'] ?? null),
                                    'role_code' => $apiUser['role_code'] ?? ($userData['role_code'] ?? null),
                                    'create_time' => $apiUser['create_time'] ?? ($userData['create_time'] ?? null),
                                    'modify_time' => $apiUser['modify_time'] ?? ($userData['modify_time'] ?? null),
                                ];

                                // 保留既有資料並覆蓋更新
                                $merged = array_merge($userData ?? [], $standardized);
                                Session::put('user_data', $merged);
                                $userData = $merged;
                                $userId = $merged['id'] ?? null;
                                $userTierId = $merged['user_tier_id'] ?? $userId;

                                Log::info('TariffController fallback updated session user_data', [
                                    'has_id' => (bool) $userId,
                                ]);
                            }
                        } else if ($resp->status() === 401) {
                            Log::warning('TariffController fallback user info unauthorized, clearing session');
                            Session::forget(['user_authenticated','user_account','auth_token','user_data']);
                            Session::flush();
                        } else {
                            Log::warning('TariffController fallback user info failed', [
                                'status' => $resp->status(),
                                'body' => $resp->body()
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('TariffController fallback user info error', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // 從請求獲取 pile_id
            $pileId = $request->input('pile_id');

            // 檢查用戶是否已登入
            if (!$userId) {
                Log::warning('Tariff query failed: User not logged in or session expired');
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => 'Please log in first',
                    'data' => null
                ], 401);
            }

            Log::info('Tariff query request', [
                'user_id' => $userId,
                'user_tier_id' => $userTierId,
                'pile_id' => $pileId,
                'ip' => $request->ip(),
                'source' => 'session'
            ]);

            // Get user's session token for external API call
            $sessionToken = Session::get('auth_token');
            if (!$sessionToken) {
                Log::warning('Tariff query failed: No auth token in session');
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => 'Authentication token missing',
                    'data' => null
                ], 401);
            }

            // Call external API with user's session token
            $tariffData = $this->callExternalTariffAPI($userId, $userTierId, $pileId, $sessionToken);

            if (!$tariffData) {
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'Tariff information not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'code' => 0,
                'message' => 'Successfully retrieved tariff information',
                'data' => $tariffData
            ]);

        } catch (\Exception $e) {
            Log::error('Tariff query error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Internal server error, please try again later',
                'data' => null
            ], 500);
        }
    }

    /**
     * Call external tariff API
     */
    private function callExternalTariffAPI(int $userId, int $userTierId, int $pileId, string $userToken): ?array
    {
        try {
            // External API configuration
            $externalApiUrl = config('services.tariff_api.url', 'http://120.110.115.126:18081');
            $apiPath = config('services.tariff_api.endpoint', '/user/purchase/tariff');
            $apiTimeout = config('services.tariff_api.timeout', 30);
            
            // 準備 API 請求參數
            $queryParams = [
                'user_id' => $userId,
                'user_tier_id' => $userTierId, 
                'pile_id' => $pileId
            ];
            
            Log::info('API Request Details', [
                'full_url' => $externalApiUrl . $apiPath . '?' . http_build_query($queryParams),
                'method' => 'GET',
                'params' => $queryParams
            ]);
            
            // 發送 HTTP 請求到外部 API - 使用用戶的 session token
            $response = Http::timeout($apiTimeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $userToken,
                ])
                ->get($externalApiUrl . $apiPath, $queryParams);
            
            Log::info('API Response Details', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Check HTTP status code
            if (!$response->successful()) {
                Log::error('External API response error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            // Parse API response
            $apiData = $response->json();
            
            Log::info('External API response parsed', [
                'success' => $apiData['success'] ?? 'N/A',
                'code' => $apiData['code'] ?? 'N/A'
            ]);

            // Check API response format
            if (!isset($apiData['success'])) {
                Log::error('External API response format error', ['response' => $apiData]);
                return null;
            }

            // If external API response failed
            if (!$apiData['success']) {
                Log::warning('External API business logic failed', [
                    'message' => $apiData['message'] ?? 'Unknown error',
                    'code' => $apiData['code'] ?? 'UNKNOWN'
                ]);
                return null;
            }

            // Transform external API data format for frontend
            return $this->transformTariffData($apiData['data'] ?? []);

        } catch (\Exception $e) {
            Log::error('Failed to call external tariff API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'user_tier_id' => $userTierId,
                'pile_id' => $pileId,
                'has_token' => !empty($userToken),
                'token_length' => strlen($userToken ?? '')
            ]);
            return null;
        }
    }

    /**
     * Transform external API data format
     */
    private function transformTariffData(array $externalData): array
    {
        return [
            'name' => $externalData['name'] ?? 'Standard Rate',
            'price_per_kwh' => (float) ($externalData['price_per_kwh'] ?? 0),
            'time_fee_per_min' => (float) ($externalData['time_fee_per_min'] ?? 0),
            'service_fee' => (float) ($externalData['service_fee'] ?? 0),
            'currency' => $externalData['currency'] ?? 'TWD',
            'effective_from' => $externalData['effective_from'] ?? now()->toISOString(),
            'effective_to' => $externalData['effective_to'] ?? now()->addYear()->toISOString(),
        ];
    }

    /**
     * Health check - test external API connection
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $externalApiUrl = config('services.tariff_api.url', 'http://120.110.115.126:18081');
            
            $response = Http::timeout(10)->get($externalApiUrl . '/health');
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'External API connection normal',
                    'data' => [
                        'api_url' => $externalApiUrl,
                        'response_time' => $response->transferStats?->getTransferTime(),
                        'status' => $response->status()
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'External API connection abnormal',
                    'data' => [
                        'api_url' => $externalApiUrl,
                        'status' => $response->status()
                    ]
                ], 503);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot connect to external API: ' . $e->getMessage(),
                'data' => null
            ], 503);
        }
    }
}