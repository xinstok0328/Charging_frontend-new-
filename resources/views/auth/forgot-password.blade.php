<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>忘記密碼</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <!-- 右上角登入按鈕 -->
  <div class="fixed top-4 right-4 z-10">
    <a href="{{ route('login') }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
      </svg>
      返回登入
    </a>
    </div>

  <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
    <h1 class="text-2xl font-semibold text-center">忘記密碼</h1>
    <p class="text-sm text-gray-600 text-center mt-2">請輸入您的帳號，我們將協助您重置密碼</p>

    {{-- 錯誤訊息 --}}
    @if ($errors->any())
      <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 p-3 text-sm">
        @foreach ($errors->all() as $err)
          <div>• {{ $err }}</div>
        @endforeach
      </div>
    @endif

    <form id="forgotPasswordForm" class="mt-6 space-y-4">
        @csrf

        <div>
        <label for="account" class="block text-sm font-medium text-gray-700">帳號（Email）</label>
        <input id="account" name="account" type="email" autocomplete="username" required
               value="{{ old('account') }}"
               class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
        </div>

      <button type="submit"
              class="w-full rounded-xl bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700 transition-colors duration-200">
        發送密碼重置請求
      </button>
    </form>

    <!-- 調試信息 -->
    <div id="debug-info" class="mt-4 p-3 bg-gray-100 rounded text-xs text-gray-600" style="display: none;">
      <p><strong>調試信息：</strong></p>
      <p>帳號: <span id="debug-account"></span></p>
      <p>API 狀態: <span id="debug-api-status"></span></p>
      <p>API 回應: <span id="debug-api-response"></span></p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('forgotPasswordForm');
        const debugInfo = document.getElementById('debug-info');
        
        // 顯示調試信息
        debugInfo.style.display = 'block';
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const account = document.getElementById('account').value.trim();
            
            // 更新調試信息
            document.getElementById('debug-account').textContent = account;
            document.getElementById('debug-api-status').textContent = '發送中...';
            
            try {
                console.log('=== 忘記密碼 API 調試開始 ===');
                console.log('帳號:', account);
                
                // 嘗試多種可能的密碼重置 API 端點
                const possibleEndpoints = [
                    'http://120.110.115.126:18081/auth/forgot_password',
                    'http://120.110.115.126:18081/auth/reset_password',
                    'http://120.110.115.126:18081/user/forgot_password',
                    'http://120.110.115.126:18081/user/reset_password'
                ];
                
                let success = false;
                let lastError = null;
                
                for (const endpoint of possibleEndpoints) {
                    try {
                        console.log(`嘗試端點: ${endpoint}`);
                        
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                account: account,
                                email: account
                            })
                        });
                        
                        console.log(`${endpoint} 響應狀態:`, response.status);
                        const data = await response.json().catch(() => ({}));
                        console.log(`${endpoint} 響應數據:`, data);
                        
                        if (response.ok && (data.success === true || data.code === 20000)) {
                            document.getElementById('debug-api-status').textContent = `成功 (${endpoint})`;
                            document.getElementById('debug-api-response').textContent = JSON.stringify(data, null, 2);
                            
                            alert(`密碼重置請求已發送！\n\n端點: ${endpoint}\n訊息: ${data.message || '請檢查您的信箱'}`);
                            success = true;
                            break;
                        } else {
                            lastError = `${endpoint}: ${data.message || '未知錯誤'}`;
                        }
                        
                    } catch (error) {
                        console.error(`${endpoint} 錯誤:`, error);
                        lastError = `${endpoint}: ${error.message}`;
                    }
                }
                
                if (!success) {
                    console.log('所有密碼重置端點都失敗了');
                    document.getElementById('debug-api-status').textContent = '所有端點都失敗';
                    document.getElementById('debug-api-response').textContent = lastError || '所有端點都無法連接';
                    
                    alert(`密碼重置功能暫時無法使用\n\n最後錯誤: ${lastError}\n\n建議：\n1. 聯繫管理員重置密碼\n2. 或使用不同的 email 重新註冊`);
                }
                
                console.log('=== 忘記密碼 API 調試結束 ===');
                
            } catch (error) {
                console.error('忘記密碼 API 錯誤:', error);
                document.getElementById('debug-api-status').textContent = '錯誤';
                document.getElementById('debug-api-response').textContent = error.message;
                alert('系統錯誤: ' + error.message);
            }
        });
    });
    </script>
    
    <!-- 底部連結 -->
    <div class="mt-6 text-center space-y-2">
      <p class="text-sm text-gray-600">
        還記得密碼？
        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
          返回登入
        </a>
      </p>
      <p class="text-sm text-gray-600">
        沒有帳號？
        <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
          立即註冊
        </a>
      </p>
    </div>
  </div>
</body>
</html>