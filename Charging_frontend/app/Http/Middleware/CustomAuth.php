<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 檢查用戶是否已登入
        $isAuthenticated = Session::get('user_authenticated', false);
        $hasToken = !empty(Session::get('auth_token'));
        
        // 檢查 Authorization header 中的 Bearer token
        $authHeader = $request->header('Authorization');
        $hasBearerToken = false;
        
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7); // 移除 "Bearer " 前綴
            $hasBearerToken = !empty($token);
            
            // 如果前端發送了 Bearer token，將其存儲到 Session 中
            if ($hasBearerToken) {
                Session::put('auth_token', $token);
                Session::put('user_authenticated', true);
                
                // 設置基本的用戶數據（如果 Session 中沒有）
                if (!Session::has('user_data')) {
                    // 從 token 中解析用戶信息（如果可能）
                    try {
                        $parts = explode('.', $token);
                        if (count($parts) === 3) {
                            $payload = json_decode(base64_decode($parts[1]), true);
                            if ($payload && isset($payload['sub'])) {
                                Session::put('user_data', [
                                    'id' => $payload['sub'],
                                    'account' => $payload['sub'] ?? 'unknown'
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        // 如果無法解析 token，設置預設值
                        Session::put('user_data', [
                            'id' => 1,
                            'account' => 'user'
                        ]);
                    }
                }
                
                $isAuthenticated = true;
                $hasToken = true;
            }
        }
        
        if (!$isAuthenticated && !$hasToken && !$hasBearerToken) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => '未登入或找不到使用者資料',
                    'data' => null
                ], 401);
            }
            
            return redirect('/login')->with('error', '請先登入');
        }

        return $next($request);
    }
}
