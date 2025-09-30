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
        
        if (!$isAuthenticated && !$hasToken) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '請先登入',
                ], 401);
            }
            
            return redirect('/login')->with('error', '請先登入');
        }

        return $next($request);
    }
}
