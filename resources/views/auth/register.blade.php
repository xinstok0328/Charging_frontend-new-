{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        建立新帳號。標有「※」為必填。
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- 你的自訂欄位（目前僅前端顯示，後端尚未寫入） --}}
        <div>
            <x-input-label for="account" value="帳號（建議用 Email）" />
            <x-text-input id="account" name="account" type="text"
                class="block mt-1 w-full" :value="old('account')" autocomplete="username"/>
            <p class="text-xs text-gray-500 mt-1">目前後端未使用此欄位，若要作為登入帳號可再調整。</p>
        </div>

        <div>
            <x-input-label for="name" value="※ 姓名" />
            <x-text-input id="name" name="name" type="text"
                class="block mt-1 w-full" :value="old('name')" required autofocus autocomplete="name"/>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="birthday" value="生日（YYYY-MM-DD）" />
            <x-text-input id="birthday" name="birthday" type="date"
                class="block mt-1 w-full" :value="old('birthday')" />
        </div>

        <div>
            <x-input-label for="email" value="※ Email" />
            <x-text-input id="email" name="email" type="email"
                class="block mt-1 w-full" :value="old('email')" required autocomplete="username"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" value="手機" />
            <x-text-input id="phone" name="phone" type="tel"
                class="block mt-1 w-full" :value="old('phone')" />
        </div>


        {{-- Breeze 預設會處理的欄位 --}}
        <div class="pt-2">
            <x-input-label for="password" value="※ 密碼" />
            <x-text-input id="password" name="password" type="password"
                class="block mt-1 w-full" required autocomplete="new-password"/>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="※ 確認密碼" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                class="block mt-1 w-full" required autocomplete="new-password"/>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                已有帳號？前往登入
            </a>
            <x-primary-button>建立帳號</x-primary-button>
        </div>
    </form>
</x-guest-layout>
