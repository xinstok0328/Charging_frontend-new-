<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Exception as QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ReservationController extends Controller
{
    public function top(): JsonResponse
    {
        $token = Session::get('auth_token');
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'unauthenticated'], 401);
        }
        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
        $endpoint = rtrim($base, '/') . '/user/purchase/top';
        $resp = \Illuminate\Support\Facades\Http::timeout(15)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get($endpoint);
        return response()->json($resp->json(), $resp->status());
    }

    public function cancel(): JsonResponse
    {
        $token = Session::get('auth_token');
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'unauthenticated'], 401);
        }
        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
        $endpoint = rtrim($base, '/') . '/user/purchase/cancel';
        $resp = \Illuminate\Support\Facades\Http::timeout(15)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->delete($endpoint);
        // Some backends respond 204 No Content for DELETE
        if ($resp->status() === 204) {
            return response()->json([
                'success' => true,
                'code' => 20000,
                'message' => 'success',
                'data' => null,
            ], 200);
        }

        // Try JSON, otherwise wrap raw text
        $json = null;
        try { $json = $resp->json(); } catch (\Throwable $e) {}
        if ($json === null) {
            $json = [
                'success' => $resp->successful(),
                'code' => $resp->status(),
                'message' => $resp->body(),
                'data' => null,
            ];
        }
        return response()->json($json, $resp->status());
    }
    public function store(StoreReservationRequest $request): JsonResponse
    {
        // Inject user_id from session/external auth
        $userData = Session::get('user_data');
        $userId = $userData['id'] ?? null;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => '未登入或找不到使用者資料',
                'data' => null,
            ], 401);
        }

        $pileId = (int) $request->input('pile_id');
        $start = Carbon::parse($request->input('start_time'))->utc();
        $end = Carbon::parse($request->input('end_time'))->utc();

        // Optional: verify pile existence via external API
        // 按你的最小規則，不做嚴格存在性檢查（避免 404 阻擋預約流程）。
        // 若後續需要再開啟校驗，可將下方 return 註解解除。
        try {
            $exists = $this->verifyPileExists($pileId);
            if (!$exists) {
                Log::info('Skip strict pile existence check; proceed with reservation', ['pile_id' => $pileId]);
            }
        } catch (\Throwable $e) {
            Log::warning('Pile existence check error (ignored)', ['error' => $e->getMessage()]);
        }

        try {
            // Forward to external backend
            $token = Session::get('auth_token');
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => 'unauthenticated',
                    'data' => null,
                ], 401);
            }

            $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
            $endpoint = rtrim($base, '/') . '/user/purchase/reserve';

            // External API expects LocalDateTime (no timezone letter 'Z')
            $tz = config('app.reservation_tz', 'Asia/Taipei');
            $fmtLocal = function (Carbon $t) use ($tz) {
                return $t->clone()->setTimezone($tz)->format('Y-m-d\TH:i:s');
            };
            $payload = [
                'pile_id' => $pileId,
                'pileId' => $pileId,
                'start_time' => $fmtLocal($start),
                'startTime' => $fmtLocal($start),
                'end_time' => $fmtLocal($end),
                'endTime' => $fmtLocal($end),
            ];

            Log::info('Forward reservation to backend', [
                'endpoint' => $endpoint,
                'payload' => $payload,
            ]);

            $resp = \Illuminate\Support\Facades\Http::timeout(20)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($endpoint, $payload);

            if (!$resp->successful()) {
                return response()->json([
                    'success' => false,
                    'code' => $resp->status(),
                    'message' => $resp->json('message') ?? 'server_error',
                    'data' => $resp->json('data') ?? null,
                ], $resp->status());
            }

            $json = $resp->json();
            return response()->json($json, 200);
        } catch (\Throwable $e) {
            Log::error('Forward reservation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'server_error',
                'data' => null,
            ], 500);
        }
    }

    private function verifyPileExists(int $pileId): bool
    {
        // For now, rely on map backend index with stationId filter.
        $backendBaseUrl = config('app.charger_api_base', env('CHARGER_API_BASE', 'http://120.110.115.126:18081'));
        $apiUrl = rtrim($backendBaseUrl, '/') . '/index';

        $resp = \Illuminate\Support\Facades\Http::timeout(8)->get($apiUrl, [
            'lat' => 0,
            'lng' => 0,
            'distance' => 0,
            'stationId' => $pileId,
        ]);
        if (!$resp->successful()) {
            return false;
        }
        $json = $resp->json();
        $data = $json['data'] ?? [];
        return collect($data)->contains(function ($i) use ($pileId) {
            return (int) ($i['id'] ?? 0) === $pileId;
        });
    }
}


