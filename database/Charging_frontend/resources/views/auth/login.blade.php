<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>登入</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <!-- 右上角註冊按鈕 -->
  <div class="fixed top-4 right-4 z-10">
    <a href="{{ route('register') }}" 
       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-md">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
      </svg>
      註冊新帳號
    </a>
  </div>

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
               class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">密碼（password）</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required
               class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
      </div>

      <button type="submit"
              class="w-full rounded-xl bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700 transition-colors duration-200">
        登入
      </button>
    </form>
    
    <!-- 登入框底部的註冊連結 -->
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        還沒有帳號？
        <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
          立即註冊
        </a>
      </p>
    </div>
  </div>
</body>
</html>