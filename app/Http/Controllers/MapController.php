<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class MapController extends Controller
{
    /**
     * 顯示地圖頁面
     * 對應路由: GET /map
     */
    public function index()
    {
        return view('map');
    }

    /**
     * 原有的 markers 方法 - 保持向後相容
     * 對應路由: GET /map/markers
     */
    public function markers(Request $request): JsonResponse
    {
        return $this->getStations($request);
    }

    /**
     * 取得充電站資料 - 純後端API版本
     * 對應路由: GET /index
     */
    public function getStations(Request $request): JsonResponse
    {
        try {
            // 驗證請求參數
            $validated = $request->validate([
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
                'distance' => 'nullable|numeric|min:0|max:100',
                'stationId' => 'nullable|integer'
            ]);

            $lat = $validated['lat'];
            $lng = $validated['lng'];
            $distance = $validated['distance'] ?? 10;
            $stationId = $validated['stationId'] ?? null;

            // 直接呼叫後端 API
            return $this->callBackendAPI($lat, $lng, $distance, $stationId);

        } catch (ValidationException $e) {
            Log::warning('參數驗證失敗', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'code' => 400,
                'message' => '參數驗證失敗: ' . collect($e->errors())->flatten()->implode(', '),
                'data' => null
            ], 400);
        } catch (\Exception $e) {
            Log::error('處理請求失敗: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => '伺服器錯誤: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
 * 呼叫後端 API
 */
private function callBackendAPI($lat, $lng, $distance, $stationId = null): JsonResponse
{
    try {
        $backendBaseUrl = config('app.charger_api_base', env('CHARGER_API_BASE', 'http://120.110.115.126:18081'));
        $apiUrl = $backendBaseUrl . '/index';

        // 建構查詢參數
        $queryParams = [
            'lat' => sprintf('%.6f', (float)$lat),
            'lng' => sprintf('%.6f', (float)$lng),
            'distance' => (int)$distance  // ✅ 修正：確保為整數，不帶小數點
        ];
        
        if ($stationId !== null) {
            $queryParams['stationId'] = (int)$stationId;
        }

        Log::info('呼叫後端API', [
            'url' => $apiUrl,
            'params' => $queryParams
        ]);

        // 發送HTTP請求到後端API
        $response = Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->get($apiUrl, $queryParams);

        // 檢查HTTP狀態碼
        if (!$response->successful()) {
            Log::error('後端API回應錯誤', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $apiUrl,
                'params' => $queryParams
            ]);
            
            return response()->json([
                'success' => false,
                'code' => $response->status(),
                'message' => '後端API錯誤 (HTTP ' . $response->status() . '): ' . $response->body(),
                'data' => null
            ], 500);
        }

        $apiData = $response->json();

        // 檢查API回應格式
        if (!isset($apiData['success']) || $apiData['success'] !== true) {
            Log::error('後端API回應格式錯誤', ['response' => $apiData]);
            
            return response()->json([
                'success' => false,
                'code' => $apiData['code'] ?? 500,
                'message' => $apiData['message'] ?? '後端API回應格式錯誤',
                'data' => null
            ], 500);
        }

        // 格式化後端API的資料
        $formattedStations = collect($apiData['data'] ?? [])->map(function ($station) {
            return [
                'id' => (int) ($station['id'] ?? 0),
                'model' => (string) ($station['model'] ?? ''),
                'connector_type' => (string) ($station['connector_type'] ?? ''),
                'max_kw' => (float) ($station['max_kw'] ?? 0),
                'firmware_version' => (string) ($station['firmware_version'] ?? ''),
                'location_address' => (string) ($station['location_address'] ?? ''),
                'lat' => (float) ($station['lat'] ?? 0),
                'lng' => (float) ($station['lng'] ?? 0),
                'distance' => round((float) ($station['distance'] ?? 0), 2)
            ];
        });

        Log::info('成功從後端API取得資料', [
            'count' => $formattedStations->count(),
            'backend_url' => $backendBaseUrl
        ]);

        return response()->json([
            'success' => true,
            'code' => 0,
            'message' => $apiData['message'] ?? 'success',
            'data' => $formattedStations
        ]);

    } catch (\Exception $e) {
        Log::error('呼叫後端API失敗: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'params' => compact('lat', 'lng', 'distance', 'stationId')
        ]);

        return response()->json([
            'success' => false,
            'code' => 500,
            'message' => '無法連接後端API: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}

    // /**
    //  * 測試後端API連接
    //  * 對應路由: GET /map/test-api（需要在 auth.php 中定義）
    //  */
    // public function testBackendAPI(Request $request): JsonResponse
    // {
    //     // 使用台中市中心座標作為測試
    //     $testLat = 24.1477;
    //     $testLng = 120.6736;
    //     $testDistance = 10;

    //     Log::info('測試後端API連接', [
    //         'test_lat' => $testLat,
    //         'test_lng' => $testLng
    //     ]);

    //     return $this->callBackendAPI($testLat, $testLng, $testDistance);
    // }
}