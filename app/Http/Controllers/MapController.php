<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class MapController extends Controller
{
    public function index()
    {
        return view('map');
    }

    public function markers()
    {
        // 模擬資料
        $data = [
            ['name' => '台北101', 'lat' => 25.033964, 'lng' => 121.564472],
            ['name' => '台北車站', 'lat' => 25.047924, 'lng' => 121.517081],
        ];
        return response()->json($data);
    }

    public function save(Request $request)
    {
        // 儲存點位資料（你可以寫進資料庫）
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // 這裡你可以寫入 DB，暫時只回傳確認
        return response()->json(['message' => '座標已儲存', 'lat' => $lat, 'lng' => $lng]);
    }
//     // 顯示地圖頁（載入 Google Maps）
//     //public function map()
//    // {
//     //    $key = Config::get('services.google.maps_key');
//      //   abort_if(empty($key), 500, '缺少 GOOGLE_MAPS_API_KEY');
//      //   return view('map', ['googleMapsKey' => $key]);
//    // }

//     // 代理呼叫你現有後端的 /index，並把結果回傳給前端
//     public function nearby(Request $request)
//     {
//         // 驗證查詢參數
//         $data = $request->validate([
//             'lat'       => ['required', 'numeric'],
//             'lng'       => ['required', 'numeric'],
//             'distance'  => ['nullable', 'numeric'], // 單位：公里（依你後端約定）
//             'stationId' => ['nullable', 'integer'],
//         ]);

//         // 預設半徑（公里）
//         if (!isset($data['distance'])) {
//             $data['distance'] = 3;
//         }

//         $base = Config::get('services.charger_api.base');
//         abort_if(empty($base), 500, '缺少 CHARGER_API_BASE');

//         // 轉發 Authorization（若你的後端需要 Token）
//         $headers = ['Accept' => 'application/json'];
//         if ($request->hasHeader('Authorization')) {
//             $headers['Authorization'] = $request->header('Authorization');
//         }

//         // 呼叫既有後端 /index
//         $resp = Http::withHeaders($headers)
//             ->timeout(10)
//             ->get($base . '/index', $data);

//         // 網路層錯誤
//         if ($resp->failed()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => '代理呼叫後端失敗',
//                 'http_status' => $resp->status(),
//                 'body' => $resp->json() ?: $resp->body(),
//             ], 502);
//         }

//         // 後端應回：{ success, code, message, data: [...] }
//         $json = $resp->json();

//         // 簡單防呆：統一輸出格式給前端地圖
//         return response()->json([
//             'success' => (bool)($json['success'] ?? false),
//             'message' => $json['message'] ?? null,
//             'data'    => array_map(function ($item) {
//                 return [
//                     'id'                => $item['id'] ?? null,
//                     'model'             => $item['model'] ?? '',
//                     'connector_type'    => $item['connector_type'] ?? '',
//                     'max_kw'            => $item['max_kw'] ?? null,
//                     'firmware_version'  => $item['firmware_version'] ?? '',
//                     'location_address'  => $item['location_address'] ?? '',
//                     'lat'               => isset($item['lat']) ? (float)$item['lat'] : null,
//                     'lng'               => isset($item['lng']) ? (float)$item['lng'] : null,
//                     'distance'          => isset($item['distance']) ? (float)$item['distance'] : null,
//                 ];
//             }, $json['data'] ?? []),
//         ]);
//     }
}
