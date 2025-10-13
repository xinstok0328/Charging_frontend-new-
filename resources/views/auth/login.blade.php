<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
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

    {{-- 前端顯示的錯誤訊息區域 --}}
    <div id="errorMessage" class="hidden mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 p-3 text-sm"></div>
    
    {{-- 成功訊息區域 --}}
    <div id="successMessage" class="hidden mt-4 rounded-lg border border-green-200 bg-green-50 text-green-700 p-3 text-sm"></div>

    {{-- 後端錯誤訊息（後端驗證/登入失敗）- 保留以防萬一 --}}
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

    <form id="loginForm" class="mt-6 space-y-4" novalidate>
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

      <button type="submit" id="loginBtn"
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

 <script>
// 取得 CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// 顯示錯誤訊息
function showError(message) {
  const errorDiv = document.getElementById('errorMessage');
  errorDiv.textContent = message;
  errorDiv.classList.remove('hidden');
  
  // 隱藏成功訊息
  document.getElementById('successMessage').classList.add('hidden');
}

// 顯示成功訊息
function showSuccess(message) {
  const successDiv = document.getElementById('successMessage');
  successDiv.textContent = message;
  successDiv.classList.remove('hidden');
  
  // 隱藏錯誤訊息
  document.getElementById('errorMessage').classList.add('hidden');
}

// 隱藏所有訊息
function hideMessages() {
  document.getElementById('errorMessage').classList.add('hidden');
  document.getElementById('successMessage').classList.add('hidden');
}

// 處理登入表單提交
document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  hideMessages();
  
  const loginBtn = document.getElementById('loginBtn');
  const originalBtnText = loginBtn.textContent;
  
  // 取得表單資料
  const account = document.getElementById('account').value.trim();
  const password = document.getElementById('password').value;
  
  // 基本驗證
  if (!account || !password) {
    showError('請填寫帳號和密碼');
    return;
  }
  
  // 禁用按鈕並顯示載入狀態
  loginBtn.disabled = true;
  loginBtn.textContent = '登入中...';
  
  try {
    console.log('===== 開始登入 =====');
    console.log('帳號:', account);
    
    // 發送登入請求
    const response = await fetch('{{ route('auth.login') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        account: account,
        password: password
      })
    });
    
    console.log('===== 收到回應 =====');
    console.log('Status:', response.status);
    
    const data = await response.json();
    console.log('完整回應:', data);
    console.log('data.success:', data.success);
    console.log('data.data:', data.data);
    
    if (response.ok && data.success) {
      console.log('===== 登入成功 =====');
      console.log('完整回應:', data);
      
      // ✅ 關鍵：從 data.data.token 取得 token
      if (data.data && data.data.token) {
        const token = data.data.token;
        
        console.log('===== 準備儲存 Token =====');
        console.log('Token 長度:', token.length);
        console.log('Token 前 20 字:', token.substring(0, 20));
        
        // ✅ 步驟 1：清除舊的 token（避免殘留）
        localStorage.removeItem('auth_token');
        console.log('✅ 已清除舊 token');
        
        // ✅ 步驟 2：儲存新 token
        localStorage.setItem('auth_token', token);
        console.log('✅ Token 已儲存到 localStorage');
        
        // ✅ 步驟 3：立即驗證（重要！）
        const verifyToken = localStorage.getItem('auth_token');
        console.log('===== 驗證儲存結果 =====');
        console.log('驗證是否存在:', verifyToken !== null);
        console.log('驗證長度:', verifyToken ? verifyToken.length : 0);
        console.log('驗證完全一致:', verifyToken === token);
        
        // ✅ 步驟 4：如果驗證失敗，停止並顯示錯誤
        if (!verifyToken || verifyToken !== token) {
          console.error('❌ 嚴重錯誤：Token 儲存失敗！');
          showError('Token 儲存失敗，這可能是瀏覽器設定問題。請檢查是否啟用 Cookie 和本地儲存。');
          loginBtn.disabled = false;
          loginBtn.textContent = originalBtnText;
          return; // ❌ 停止執行，不要跳轉
        }
        
        console.log('✅ Token 驗證成功');
        
      } else {
        console.error('❌ 錯誤：後端未返回 token');
        console.log('data 結構:', JSON.stringify(data, null, 2));
        showError('登入成功但未收到認證資訊，請重試');
        loginBtn.disabled = false;
        loginBtn.textContent = originalBtnText;
        return;
      }
      
      // 顯示成功訊息
      showSuccess(data.message || '登入成功！即將跳轉...');
      
      // ✅ 步驟 5：延遲跳轉前再次確認
      setTimeout(() => {
        console.log('===== 跳轉前最終檢查 =====');
        const finalCheck = localStorage.getItem('auth_token');
        console.log('Token 仍然存在:', finalCheck !== null);
        console.log('Token 長度:', finalCheck ? finalCheck.length : 0);
        
        if (finalCheck) {
          console.log('✅ 確認完成，準備跳轉');
          window.location.href = '/map';
        } else {
          console.error('❌ 嚴重錯誤：Token 在跳轉前消失了！');
          showError('Token 儲存異常，請檢查瀏覽器設定或嘗試無痕模式');
          loginBtn.disabled = false;
          loginBtn.textContent = originalBtnText;
        }
      }, 1500);
      
    } else {
      // ❌ 登入失敗
      console.log('===== 登入失敗 =====');
      let errorMessage = '登入失敗';
      
      if (data.message) {
        errorMessage = data.message;
      } else if (data.errors) {
        // 處理驗證錯誤
        const errors = Object.values(data.errors).flat();
        errorMessage = errors.join('\n');
      }
      
      console.error('錯誤訊息:', errorMessage);
      showError(errorMessage);
      
      // 恢復按鈕
      loginBtn.disabled = false;
      loginBtn.textContent = originalBtnText;
    }
    
  } catch (error) {
    console.error('===== 登入過程發生錯誤 =====');
    console.error('錯誤類型:', error.name);
    console.error('錯誤訊息:', error.message);
    console.error('完整錯誤:', error);
    
    showError('網路連線錯誤，請檢查網路狀態');
    
    // 恢復按鈕
    loginBtn.disabled = false;
    loginBtn.textContent = originalBtnText;
  }
});
</script>
</body>
</html>