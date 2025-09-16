<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExternalAuthController extends Controller
{
    /** 取得外部 API base（來自 config/services.php → .env: EXT_API_BASE） */
    protected function apiBase(): string
    {
        return rtrim(config('services.extapi.base'), '/');
    }

    /* -------------------- Register -------------------- */

    /** 顯示註冊頁（沿用你現有 Blade） */
    public function showRegister()
    {
        return view('auth.register');
    }

    /** 送出註冊到外部 API */
    public function doRegister(Request $request)
    {
        // 這裡用 Breeze 預設規則；若外部 API 規則不同可調整
        $data = $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email'],
            'password'              => ['required','string','min:8','confirmed'],
        ]);

        try {
            $res = Http::timeout(15)
                ->acceptJson()
                ->post($this->apiBase().'/auth/register', $data);
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['api' => '外部註冊服務無法連線：'.$e->getMessage()])
                ->withInput();
        }

        if ($res->status() === 422) {
            // 盡量把對方的欄位錯誤丟回 Blade
            $errors = $res->json('errors') ?? ['api' => [$res->json('message') ?? '驗證失敗']];
            throw ValidationException::withMessages($errors);
        }

        if ($res->failed()) {
            return back()
                ->withErrors(['api' => '註冊失敗（HTTP '.$res->status().'）：'.($res->json('message') ?? $res->body())])
                ->withInput();
        }

        // 註冊成功後導回登入頁（也可改成自動登入：看你的外部 API 是否回 token）
        return redirect()->route('login')->with('status', '註冊成功，請登入。');
    }

    /* -------------------- Login -------------------- */

    /** 顯示登入頁 */
    public function showLogin()
    {
        return view('auth.login');
    }

    /** 送出登入到外部 API，成功後記本機 Session 與外部 Token */
    public function doLogin(Request $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        try {
            $res = Http::timeout(15)
                ->acceptJson()
                ->post($this->apiBase().'/auth/login', [
                    'email'    => $request->input('email'),
                    'password' => $request->input('password'),
                ]);
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['api' => '外部登入服務無法連線：'.$e->getMessage()])
                ->withInput($request->only('email'));
        }

        if ($res->status() === 422) {
            $errors = $res->json('errors') ?? ['api' => [$res->json('message') ?? '驗證失敗']];
            throw ValidationException::withMessages($errors);
        }

        if ($res->failed()) {
            // 常見為 401/403
            return back()
                ->withErrors(['email' => $res->json('message') ?? '帳號或密碼不正確'])
                ->withInput($request->only('email'));
        }

        $json    = $res->json() ?? [];
        $token   = data_get($json, 'token') ?? data_get($json, 'access_token');
        $profile = data_get($json, 'user', []);

        // 將外部 token 存到 Session，登出時要帶出去
        if ($token) {
            session(['ext_token' => $token]);
        }

        // 為了讓 Laravel 的 'auth' 中介層可用，建立/更新一份本機 User 並登入
        $email = $profile['email'] ?? $request->input('email');
        $name  = $profile['name']  ?? (explode('@', $email)[0] ?? 'User');

        /** @var \App\Models\User $user */
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                // 給一個隨機 hash（外部驗證，不會用到本機密碼）
                'password' => Hash::make(Str::random(40)),
            ]
        );

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 導回預設首頁（RouteServiceProvider::HOME），或自行改目的地
        return redirect()->intended(config('app.home', \App\Providers\RouteServiceProvider::HOME));
    }

    /* -------------------- Logout -------------------- */

    /** 代理登出外部 API，並清除本機 Session */
    public function externalLogout(Request $request)
    {
        $token = session('ext_token');
        $url   = $this->apiBase().'/auth/logout';

        try {
            Log::info('External logout calling', ['url' => $url, 'hasToken' => (bool)$token]);

            if ($token) {
                // 大多數後端會要求 Bearer Token
                $res = Http::timeout(10)->withToken($token)->post($url);
                Log::info('External logout response', ['status' => $res->status(), 'body' => $res->body()]);
            } else {
                // 若對方不需要 token，可直接打；否則略過
                Http::timeout(10)->post($url);
            }
        } catch (\Throwable $e) {
            Log::warning('External logout failed', ['error' => $e->getMessage()]);
        }

        // 清本機登入狀態與 Session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('ext_token');

        return redirect()->route('login')->with('status', '您已登出');
    }
}
