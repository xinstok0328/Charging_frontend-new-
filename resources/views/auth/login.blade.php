<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>登入</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
    <h1 class="text-2xl font-semibold text-center">帳號登入</h1>


    {{-- 錯誤訊息（後端驗證/登入失敗） --}}
    @if ($errors->any())
      <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 p-3 text-sm">
        @foreach ($errors->all() as $err)
          <div>• {{ $err }}</div>
        @endforeach
      </div>
    @endif
    @if (session('login_error'))
      <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 p-3 text-sm">
        {{ session('login_error') }}
      </div>
    @endif

    <form class="mt-6 space-y-4" method="POST" action="{{ route('auth.login') }}" novalidate>
      @csrf

      <div>
        <label for="account" class="block text-sm font-medium text-gray-700">帳號（account）</label>
        <input id="account" name="account" type="text" autocomplete="username" required
               value="{{ old('account') }}"
               class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">密碼（password）</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required
               class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
      </div>

      <button type="submit"
              class="w-full rounded-xl bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700">
        登入


      </button>
    </form>
    
  
    </p>
  </div>
</body>
</html>
