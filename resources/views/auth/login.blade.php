{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        請輸入 Email 與密碼登入。
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4" id="loginForm">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email"
                class="block mt-1 w-full" :value="old('email')" required autofocus
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="密碼" />
            <x-text-input id="password" name="password" type="password"
                class="block mt-1 w-full" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember" class="inline-flex items-center">
                <input id="remember" type="checkbox" name="remember"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-600">記住我</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900"
                   href="{{ route('password.request') }}">忘記密碼？</a>
            @endif>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900"
               href="{{ route('register') }}">還沒有帳號？前往註冊</a>

            <x-primary-button>登入</x-primary-button>
        </div>
    </form>
</x-guest-layout>
