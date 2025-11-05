<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'emailCodeOK' => ['required', 'in:1'], // 確保驗證碼已通過
        ]);

        // 調用外部 API 進行註冊
        $base = config('services.backend.base_url', env('BACKEND_BASE_URL', 'http://120.110.115.126:18081'));
        $endpoint = rtrim($base, '/') . '/auth/register';

        try {
            Log::info('Register API Request', [
                'endpoint' => $endpoint,
                'email' => $request->email
            ]);

            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'account' => $request->email,
                    'password' => $request->password,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->input('phone', ''),
                    'file_id' => 0
                ]);

            Log::info('Register API Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

        } catch (\Throwable $e) {
            Log::error('External register HTTP exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withErrors(['register_error' => '註冊服務暫時無法使用，請稍後再試'])
                ->withInput($request->except('password'));
        }

        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? '註冊失敗';

            Log::warning('Register API failed', [
                'status' => $response->status(),
                'response' => $errorData
            ]);

            return back()
                ->withErrors(['register_error' => $errorMessage])
                ->withInput($request->except('password'));
        }

        $registerData = $response->json();

        if (isset($registerData['success']) && $registerData['success'] === true) {
            // 註冊成功，自動登入
            return redirect('/login')->with('success', '註冊成功！請使用您的帳號密碼登入');
        } else {
            $errorMessage = $registerData['message'] ?? '註冊失敗';
            return back()
                ->withErrors(['register_error' => $errorMessage])
                ->withInput($request->except('password'));
        }
    }
}
