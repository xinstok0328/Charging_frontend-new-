<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>重置密碼</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <!-- 右上角返回按鈕 -->
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
    <h1 class="text-2xl font-semibold text-center">重置密碼</h1>
    <p class="text-sm text-gray-600 text-center mt-2">請輸入驗證碼和新密碼來重置您的密碼</p>

    {{-- 錯誤訊息 --}}
    <div id="error-message" class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 p-3 text-sm" style="display: none;">
    </div>

    {{-- 成功訊息 --}}
    <div id="success-message" class="mt-4 rounded-lg border border-green-200 bg-green-50 text-green-700 p-3 text-sm" style="display: none;">
    </div>

    <form id="resetPasswordForm" class="mt-6 space-y-4">
        {{-- 帳號（Email）欄位 --}}
        <div>
            <label for="account" class="block text-sm font-medium text-gray-700">帳號（Email）</label>
            <input id="account" name="account" type="email" autocomplete="username" required
                   class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border"
                   placeholder="請輸入您的 Email">
        </div>

        {{-- 驗證碼欄位 --}}
        <div>
            <label for="verifyCode" class="block text-sm font-medium text-gray-700">驗證碼</label>
            <div class="mt-1 flex items-start gap-2">
                <input id="verifyCode" name="verifyCode" type="text" required
                       placeholder="請輸入收到的驗證碼"
                       class="flex-1 rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border">
                <button
                    id="getCodeBtn"
                    type="button"
                    class="px-3 py-2 bg-blue-500 text-white rounded-xl whitespace-nowrap text-center text-sm"
                    style="min-width: 140px; width: 140px;"
                >
                    取得驗證碼
                </button>
            </div>
            <div id="verify-status" class="mt-2 text-sm"></div>
        </div>

        {{-- 新密碼欄位 --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">新密碼</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="mt-1 w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2 border"
                   placeholder="請輸入新密碼">
        </div>

        {{-- 提交按鈕 --}}
        <button type="submit"
                id="submitBtn"
                class="w-full rounded-xl bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
          重置密碼
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
        const form = document.getElementById('resetPasswordForm');
        const debugInfo = document.getElementById('debug-info');
        const errorDiv = document.getElementById('error-message');
        const successDiv = document.getElementById('success-message');
        const API_BASE = 'http://120.110.115.126:18081';
        
        // 顯示調試信息
        debugInfo.style.display = 'block';
        
        // 取得驗證碼功能
        document.getElementById('getCodeBtn').addEventListener('click', async function() {
            const email = document.getElementById('account').value.trim();
            const verifyStatus = document.getElementById('verify-status');
            const getCodeBtn = document.getElementById('getCodeBtn');
            
            // Email 格式檢查
            const isEmail = (s) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);
            
            if (!email) {
                verifyStatus.innerHTML = '<span class="text-red-500">請先輸入 Email</span>';
                return;
            }
            
            if (!isEmail(email)) {
                verifyStatus.innerHTML = '<span class="text-red-500">Email 格式不正確</span>';
                return;
            }
            
            try {
                verifyStatus.innerHTML = '<span class="text-blue-500">正在發送驗證碼…</span>';
                getCodeBtn.disabled = true;
                getCodeBtn.textContent = '發送中…';
                
                const url = `${API_BASE}/auth/send_mail_code?loginMail=${encodeURIComponent(email)}`;
                const res = await fetch(url, { method: 'GET', headers: { Accept: '*/*' } });
                const data = await res.json().catch(() => ({}));
                
                if (res.ok && (data?.success === true || data?.code === 20000)) {
                    verifyStatus.innerHTML = `<span class="text-green-500">${data?.message || '驗證碼已寄出，請到信箱查收！'}</span>`;
                    
                    // 開始倒數計時（使用固定格式避免跳動）
                    let left = 60;
                    const timer = setInterval(() => {
                        if (left >= 10) {
                            getCodeBtn.textContent = `${left}s 後可再發送`;
                        } else {
                            getCodeBtn.textContent = ` ${left}s 後可再發送`;
                        }
                        left--;
                        if (left < 0) {
                            clearInterval(timer);
                            getCodeBtn.disabled = false;
                            getCodeBtn.textContent = '取得驗證碼';
                        }
                    }, 1000);
                } else {
                    verifyStatus.innerHTML = `<span class="text-red-500">${data?.message || `發送失敗（HTTP ${res.status}）`}</span>`;
                    getCodeBtn.disabled = false;
                    getCodeBtn.textContent = '取得驗證碼';
                }
            } catch (err) {
                verifyStatus.innerHTML = `<span class="text-red-500">發送失敗：${err?.message || err}</span>`;
                getCodeBtn.disabled = false;
                getCodeBtn.textContent = '取得驗證碼';
            }
        });
        
        // 表單提交 - 調用 force_update_pwd API
        const submitBtn = document.getElementById('submitBtn');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // 1. 前端輸入前處理
            // email: email.trim() + URL encode（根據 API 文檔，參數名稱是 email）
            const emailRaw = document.getElementById('account').value.trim();
            const email = encodeURIComponent(emailRaw);
            
            // verifyCode: code.trim() + 確認長度=6、大小寫一致、無空白（方案A）
            let verifyCodeRaw = document.getElementById('verifyCode').value.trim();
            // 去除所有空白（包括中間的空格）
            verifyCodeRaw = verifyCodeRaw.replace(/\s+/g, '');
            // 更新輸入框（移除空白後的值）
            document.getElementById('verifyCode').value = verifyCodeRaw;
            const verifyCode = verifyCodeRaw;
            
            // password: 先做基本檢核 (≥8，不要求大小寫)
            const password = document.getElementById('password').value;
            
            // 隱藏之前的訊息
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            
            // 基本驗證
            if (!emailRaw || !verifyCodeRaw || !password) {
                errorDiv.textContent = '請填寫所有必填欄位';
                errorDiv.style.display = 'block';
                return;
            }
            
            // 3. 前端檢核：驗證碼 6碼、去空白、大小寫一致、避免 O/0、l/1 混淆
            if (verifyCode.length !== 6) {
                errorDiv.textContent = '驗證碼長度必須為 6 位數';
                errorDiv.style.display = 'block';
                return;
            }
            
            // 檢查是否包含容易混淆的字符，給出警告但不阻止提交
            const hasAmbiguous = /[O0l1]/i.test(verifyCode);
            if (hasAmbiguous) {
                console.warn('⚠️ 驗證碼包含容易混淆的字符（O/0 或 l/1），請仔細確認輸入是否正確');
            }
            
            // 大小寫一致性檢查：驗證碼應該保持一致的大小寫格式
            // （這裡只是記錄，實際大小寫由用戶輸入決定，後端會驗證）
            console.log('驗證碼格式檢查:');
            console.log('  - 長度:', verifyCode.length, '(要求: 6)');
            console.log('  - 大小寫:', verifyCode);
            console.log('  - 包含易混淆字符:', hasAmbiguous ? '是（O/0 或 l/1）' : '否');
            
            // 密碼驗證：≥8（不要求大小寫）
            if (password.length < 8) {
                errorDiv.textContent = '密碼長度至少需要 8 個字元';
                errorDiv.style.display = 'block';
                return;
            }
            
            // 4. UI 行為：按鈕先 disabled，收到回應後再 enabled
            submitBtn.disabled = true;
            submitBtn.textContent = '處理中...';
            
            // 更新調試信息
            document.getElementById('debug-account').textContent = emailRaw;
            document.getElementById('debug-api-status').textContent = '發送中...';
            
            // API 端點：POST /auth/force_update_pwd
            // 根據 Swagger API 文檔：
            // - Method: POST
            // - 參數名稱：email, verifyCode, password（都在 Query string）
            // - Header: accept: */*
            // - Body: 空（所有參數都在 Query string）
            const apiEndpoint = `${API_BASE}/auth/force_update_pwd`;
            
            // 構建 Query string（所有參數都經過 URL encode）
            const queryParams = new URLSearchParams();
            queryParams.append('email', emailRaw); // email 參數使用原始值，URLSearchParams 會自動 encode
            queryParams.append('verifyCode', verifyCode);
            queryParams.append('password', password);
            
            const url = `${apiEndpoint}?${queryParams.toString()}`;
            
            try {
                console.log('=== 重置密碼 API 調試開始 ===');
                console.log('API 端點:', apiEndpoint);
                console.log('原始 Email:', emailRaw);
                console.log('驗證碼:', verifyCode);
                console.log('驗證碼長度:', verifyCode.length);
                console.log('密碼長度:', password.length);
                console.log('完整 Request URL:', url);
                
                // 根據 Swagger 文檔：POST 請求，Header 為 accept: */*，沒有 body
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'accept': '*/*'
                        // 注意：沒有 Content-Type header（因為沒有 body）
                    }
                    // 沒有 body（根據 Swagger 文檔示例 curl -d ''）
                });
                
                console.log('API 響應狀態:', response.status);
                const data = await response.json().catch(() => ({}));
                console.log('API 響應數據:', data);
                
                // 檢查成功條件：HTTP 200 且 success:true
                if (response.status === 200 && data.success === true) {
                    document.getElementById('debug-api-status').textContent = '成功';
                    document.getElementById('debug-api-response').textContent = JSON.stringify(data, null, 2);
                    
                    // 4. UI 行為：顯示 success, code, message 在結果區塊
                    const resultInfo = `
                        <div class="mt-2 text-xs space-y-1">
                            <div>success: <span class="font-mono">${data.success}</span></div>
                            <div>code: <span class="font-mono">${data.code || 'N/A'}</span></div>
                            <div>message: <span class="font-mono">${data.message || 'N/A'}</span></div>
                        </div>
                    `;
                    
                    successDiv.innerHTML = `<div class="font-medium">密碼重置成功！</div><div class="mt-1">${data.message || '您可以使用新密碼登入了。'}</div>${resultInfo}`;
                    successDiv.style.display = 'block';
                    
                    // 成功後 2 秒跳轉到登入頁面
                    setTimeout(() => {
                        window.location.href = '{{ route("login") }}';
                    }, 2000);
                } else {
                    // 處理錯誤回應
                    let errorMessage = '';
                    let errorDetails = '';
                    
                    // 4. UI 行為：如果 code=9999，顯示提示
                    if (data.code === 9999) {
                        errorMessage = '更改密碼失敗 (code: 9999)';
                        
                        // 檢查可能的原因
                        const possibleCauses = [];
                        
                        // 檢查驗證碼格式
                        if (verifyCode.length !== 6) {
                            possibleCauses.push(`驗證碼長度不正確：${verifyCode.length} 位數（應為 6 位數）`);
                        }
                        
                        // 檢查密碼長度
                        if (password.length < 8) {
                            possibleCauses.push(`密碼長度不足：${password.length} 字元（應至少 8 字元）`);
                        }
                        
                        if (possibleCauses.length === 0) {
                            possibleCauses.push('驗證碼可能已過期或已被使用');
                            possibleCauses.push('驗證碼可能輸入錯誤');
                        }
                        
                        errorDetails = `
                            <div class="mt-2 text-xs bg-yellow-50 border border-yellow-200 rounded p-3">
                                <p class="font-medium text-yellow-800 mb-2">⚠️ 可能的原因：</p>
                                <ul class="list-disc list-inside space-y-1 text-yellow-700 mb-3">
                                    ${possibleCauses.map(cause => `<li>${cause}</li>`).join('')}
                                </ul>
                                <p class="font-medium text-yellow-800 mb-1 mt-3">📋 解決步驟：</p>
                                <ol class="list-decimal list-inside space-y-1 text-yellow-700">
                                    <li><strong>重新整理頁面</strong>（清除舊狀態）</li>
                                    <li>重新點擊「取得驗證碼」</li>
                                    <li><strong>不要點「確認驗證碼」按鈕</strong></li>
                                    <li>直接填寫驗證碼和新密碼（至少 8 字元）</li>
                                    <li>點擊「重置密碼」</li>
                                </ol>
                                <p class="mt-3 text-xs text-gray-600">💡 提示：請打開瀏覽器的「開發者工具」(F12) → 「Console」查看詳細的 API 調試信息</p>
                            </div>
                        `;
                    } else if (data.message) {
                        errorMessage = data.message;
                    } else if (data.error) {
                        errorMessage = data.error;
                    } else {
                        errorMessage = `重置失敗（HTTP ${response.status}）`;
                    }
                    
                    // 記錄完整的調試信息
                    const debugResponse = `API 端點: ${apiEndpoint}\n` +
                                         `HTTP 狀態: ${response.status}\n` +
                                         `success: ${data.success || false}\n` +
                                         `code: ${data.code || '無'}\n` +
                                         `message: ${data.message || '無'}\n` +
                                         `完整回應: ${JSON.stringify(data, null, 2)}`;
                    
                    document.getElementById('debug-api-status').textContent = '失敗';
                    document.getElementById('debug-api-response').textContent = debugResponse;
                    
                    // 4. UI 行為：顯示 success, code, message
                    const resultInfo = `
                        <div class="mt-2 text-xs space-y-1">
                            <div>success: <span class="font-mono">${data.success || false}</span></div>
                            <div>code: <span class="font-mono">${data.code || 'N/A'}</span></div>
                            <div>message: <span class="font-mono">${data.message || 'N/A'}</span></div>
                        </div>
                    `;
                    
                    errorDiv.innerHTML = `<div class="font-medium">重置失敗</div><div class="mt-1">${errorMessage}</div>${resultInfo}${errorDetails}`;
                    errorDiv.style.display = 'block';
                }
                
                console.log('=== 重置密碼 API 調試結束 ===');
                
            } catch (error) {
                console.error('重置密碼 API 錯誤:', error);
                document.getElementById('debug-api-status').textContent = '錯誤';
                document.getElementById('debug-api-response').textContent = `${apiEndpoint}: ${error.message}`;
                
                errorDiv.innerHTML = `<div class="font-medium">系統錯誤</div><div class="mt-1">${error.message}</div>`;
                errorDiv.style.display = 'block';
            } finally {
                // 4. UI 行為：收到回應後再 enabled 按鈕
                submitBtn.disabled = false;
                submitBtn.textContent = '重置密碼';
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
        沒有驗證碼？
        <a href="{{ route('password.request') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
          重新申請
        </a>
      </p>
    </div>
  </div>
</body>
</html>
