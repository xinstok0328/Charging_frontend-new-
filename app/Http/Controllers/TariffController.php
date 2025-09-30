<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TariffController extends Controller
{
    /**
     * Get charging tariff information
     */
    public function getTariff(Request $request): JsonResponse
    {
        // // 調試用：立即回傳測試資料
        //     return response()->json([
        //         'debug' => true,
        //         'message' => 'Controller loaded successfully',
        //         'request_params' => $request->all(),
        //         'timestamp' => now()
        //     ]);


        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|min:1',
                'user_tier_id' => 'required|integer|min:1',
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

            $userId = $request->input('user_id');
            $userTierId = $request->input('user_tier_id');
            $pileId = $request->input('pile_id');

            Log::info('Tariff query request', [
                'user_id' => $userId,
                'user_tier_id' => $userTierId,
                'pile_id' => $pileId,
                'ip' => $request->ip()
            ]);

            // Call external API
            $tariffData = $this->callExternalTariffAPI($userId, $userTierId, $pileId);

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
    private function callExternalTariffAPI(int $userId, int $userTierId, int $pileId): ?array
    {
        try {
            // External API configuration
            $externalApiUrl = config('services.tariff_api.url', 'http://120.110.115.126:18081');
            $apiPath = config('services.tariff_api.endpoint', '/user/purchase/tariff');
            $apiTimeout = config('services.tariff_api.timeout', 30);
            
            // 嘗試不同的參數格式
        $queryParams = [
            'user_id' => $userId,
            'user_tier_id' => $userTierId, 
            'pile_id' => $pileId
        ];
        
        Log::info('API Request Details', [
            'full_url' => $externalApiUrl . $apiPath . '?' . http_build_query($queryParams),
            'method' => 'GET',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.tariff_api.token'),
                'X-API-Key' => config('services.tariff_api.token'),
            ]
        ]);
        
        $response = Http::timeout($apiTimeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.tariff_api.token'),
                'X-API-Key' => config('services.tariff_api.token'),
            ])
            ->get($externalApiUrl . $apiPath, $queryParams);
            
        Log::info('API Response Details', [
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);    

            // // Prepare API request parameters
            // $queryParams = [
            //     'user_id' => $userId,
            //     'user_tier_id' => $userTierId,
            //     'pile_id' => $pileId
            // ];

            // Log::info('Calling external tariff API', [
            //     'url' => $externalApiUrl . $apiPath,
            //     'params' => $queryParams
            // ]);

            // // Send HTTP request to external API
            // $response = Http::timeout($apiTimeout)
            //     ->withHeaders([
            //         'Accept' => 'application/json',
            //         'Content-Type' => 'application/json',
            //         'Authorization' => 'Bearer ' . config('services.tariff_api.token'),
            //         'X-API-Key' => config('services.tariff_api.token'),
            //     ])
            //     ->get($externalApiUrl . $apiPath, $queryParams);

            // // Check HTTP status code
            // if (!$response->successful()) {
            //     Log::error('External API response error', [
            //         'status' => $response->status(),
            //         'body' => $response->body()
            //     ]);
            //     return null;
            // }

            // // Parse API response
            // $apiData = $response->json();
            
            // Log::info('External API response', [
            //     'response' => $apiData
            // ]);

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
                'pile_id' => $pileId
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
     * Simple rate info endpoint for frontend AJAX
     */
    public function getRateInfo(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', 6);
        $userTierId = $request->input('user_tier_id', 6);
        $pileId = $request->input('pile_id', 6);

        return $this->getTariff($request);
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