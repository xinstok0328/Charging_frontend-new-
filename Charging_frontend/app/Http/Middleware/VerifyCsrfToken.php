<?php

namespace App\Http\Middleware;  // ✅ 必須有命名空間！

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * 要排除 CSRF 驗證的路由（測試用）
     *
     * @var array<int, string>
     */
    protected $except = [
        '*',      // ❗全域放行（僅建議測試環境使用）
        'api/*',  // 放行所有 API 路由
    ];
}
