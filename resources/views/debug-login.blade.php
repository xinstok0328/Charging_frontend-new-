<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>登入調試頁面</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <div class="w-full max-w-2xl bg-white rounded-2xl shadow p-8">
    <h1 class="text-2xl font-semibold text-center mb-6">登入調試頁面</h1>

    <!-- 測試區域 -->
    <div class="space-y-6">
      <!-- 1. 測試登入 API -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-medium mb-3">1. 測試登入 API</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">帳號</label>
            <input id="test-account" type="text" value="c1241207@ems.niu.edu.tw" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">密碼</label>
            <input id="test-password" type="password" placeholder="請輸入密碼" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <button onclick="testLogin()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            測試登入
          </button>
        </div>
        <div id="login-result" class="mt-3 p-3 bg-gray-100 rounded text-sm"></div>
      </div>

      <!-- 2. 測試註冊 API -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-medium mb-3">2. 測試註冊 API</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input id="test-register-email" type="email" value="c1241207@ems.niu.edu.tw" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">密碼</label>
            <input id="test-register-password" type="password" placeholder="請輸入密碼" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">姓名</label>
            <input id="test-register-name" type="text" value="wzw" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <button onclick="testRegister()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            測試註冊
          </button>
        </div>
        <div id="register-result" class="mt-3 p-3 bg-gray-100 rounded text-sm"></div>
      </div>

      <!-- 3. 檢查 Email 是否存在於外部 API -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-medium mb-3">3. 檢查 Email 是否存在於外部 API</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input id="test-check-email" type="email" placeholder="輸入要檢查的 email" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <button onclick="checkEmailExists()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
            檢查 Email 是否存在
          </button>
        </div>
        <div id="check-email-result" class="mt-3 p-3 bg-gray-100 rounded text-sm"></div>
      </div>

      <!-- 4. 測試驗證碼 API -->
      <div class="border rounded-lg p-4">
        <h2 class="text-lg font-medium mb-3">4. 測試驗證碼 API</h2>
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input id="test-verify-email" type="email" value="c1241207@ems.niu.edu.tw" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">驗證碼</label>
            <input id="test-verify-code" type="text" placeholder="請輸入驗證碼" 
                   class="mt-1 w-full rounded border-gray-300 px-3 py-2 border">
          </div>
          <div class="flex gap-2">
            <button onclick="testSendCode()" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
              發送驗證碼
            </button>
            <button onclick="testCheckCode()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
              檢查驗證碼
            </button>
          </div>
        </div>
        <div id="verify-result" class="mt-3 p-3 bg-gray-100 rounded text-sm"></div>
      </div>
    </div>

    <!-- 返回按鈕 -->
    <div class="mt-6 text-center">
      <a href="/login" class="text-indigo-600 hover:text-indigo-700 font-medium">
        ← 返回登入頁面
      </a>
    </div>
  </div>

  <script>
  const API_BASE = 'http://120.110.115.126:18081';

  async function testLogin() {
    const account = document.getElementById('test-account').value.trim();
    const password = document.getElementById('test-password').value.trim();
    const resultDiv = document.getElementById('login-result');
    
    if (!account || !password) {
      resultDiv.innerHTML = '<div class="text-red-500">請填寫帳號和密碼</div>';
      return;
    }

    resultDiv.innerHTML = '<div class="text-blue-500">測試中...</div>';

    try {
      console.log('=== 測試登入 API ===');
      console.log('帳號:', account);
      console.log('密碼長度:', password.length);

      const response = await fetch(`${API_BASE}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          account: account,
          password: password
        })
      });

      console.log('登入 API 響應狀態:', response.status);
      const data = await response.json().catch(() => ({}));
      console.log('登入 API 響應數據:', data);

      if (response.ok && data.success === true) {
        resultDiv.innerHTML = `
          <div class="text-green-500">
            <p>✅ 登入成功！</p>
            <p>Token: ${data.data?.token ? data.data.token.substring(0, 20) + '...' : '無'}</p>
            <p>用戶資料: ${JSON.stringify(data.data, null, 2)}</p>
          </div>
        `;
      } else {
        resultDiv.innerHTML = `
          <div class="text-red-500">
            <p>❌ 登入失敗</p>
            <p>狀態碼: ${response.status}</p>
            <p>錯誤訊息: ${data.message || '未知錯誤'}</p>
            <p>完整回應: ${JSON.stringify(data, null, 2)}</p>
          </div>
        `;
      }
    } catch (error) {
      console.error('登入測試錯誤:', error);
      resultDiv.innerHTML = `<div class="text-red-500">❌ 錯誤: ${error.message}</div>`;
    }
  }

  async function testRegister() {
    const email = document.getElementById('test-register-email').value.trim();
    const password = document.getElementById('test-register-password').value.trim();
    const name = document.getElementById('test-register-name').value.trim();
    const resultDiv = document.getElementById('register-result');
    
    if (!email || !password || !name) {
      resultDiv.innerHTML = '<div class="text-red-500">請填寫所有欄位</div>';
      return;
    }

    resultDiv.innerHTML = '<div class="text-blue-500">測試中...</div>';

    try {
      console.log('=== 測試註冊 API ===');
      console.log('Email:', email);
      console.log('密碼長度:', password.length);
      console.log('姓名:', name);

      const response = await fetch(`${API_BASE}/auth/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          account: email,
          password: password,
          name: name,
          email: email,
          phone: '0900796626',
          file_id: 0
        })
      });

      console.log('註冊 API 響應狀態:', response.status);
      const data = await response.json().catch(() => ({}));
      console.log('註冊 API 響應數據:', data);

      if (response.ok && (data.code === 20000 || data.success === true)) {
        resultDiv.innerHTML = `
          <div class="text-green-500">
            <p>✅ 註冊成功！</p>
            <p>完整回應: ${JSON.stringify(data, null, 2)}</p>
          </div>
        `;
      } else {
        resultDiv.innerHTML = `
          <div class="text-red-500">
            <p>❌ 註冊失敗</p>
            <p>狀態碼: ${response.status}</p>
            <p>錯誤訊息: ${data.message || '未知錯誤'}</p>
            <p>完整回應: ${JSON.stringify(data, null, 2)}</p>
          </div>
        `;
      }
    } catch (error) {
      console.error('註冊測試錯誤:', error);
      resultDiv.innerHTML = `<div class="text-red-500">❌ 錯誤: ${error.message}</div>`;
    }
  }

  async function testSendCode() {
    const email = document.getElementById('test-verify-email').value.trim();
    const resultDiv = document.getElementById('verify-result');
    
    if (!email) {
      resultDiv.innerHTML = '<div class="text-red-500">請填寫 Email</div>';
      return;
    }

    resultDiv.innerHTML = '<div class="text-blue-500">發送中...</div>';

    try {
      console.log('=== 測試發送驗證碼 API ===');
      console.log('Email:', email);

      const response = await fetch(`${API_BASE}/auth/send_mail_code?loginMail=${encodeURIComponent(email)}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json'
        }
      });

      console.log('發送驗證碼 API 響應狀態:', response.status);
      const data = await response.json().catch(() => ({}));
      console.log('發送驗證碼 API 響應數據:', data);

      if (response.ok && (data.success === true || data.code === 20000)) {
        resultDiv.innerHTML = `
          <div class="text-green-500">
            <p>✅ 驗證碼發送成功！</p>
            <p>訊息: ${data.message || '驗證碼已發送'}</p>
          </div>
        `;
      } else {
        resultDiv.innerHTML = `
          <div class="text-red-500">
            <p>❌ 發送失敗</p>
            <p>狀態碼: ${response.status}</p>
            <p>錯誤訊息: ${data.message || '未知錯誤'}</p>
          </div>
        `;
      }
    } catch (error) {
      console.error('發送驗證碼測試錯誤:', error);
      resultDiv.innerHTML = `<div class="text-red-500">❌ 錯誤: ${error.message}</div>`;
    }
  }

  async function checkEmailExists() {
    const email = document.getElementById('test-check-email').value.trim();
    const resultDiv = document.getElementById('check-email-result');
    
    if (!email) {
      resultDiv.innerHTML = '<div class="text-red-500">請輸入 Email</div>';
      return;
    }

    resultDiv.innerHTML = '<div class="text-blue-500">檢查中...</div>';

    try {
      console.log('=== 檢查 Email 是否存在 ===');
      console.log('Email:', email);

      const response = await fetch(`${API_BASE}/auth/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          account: email,
          password: 'test_check_password',
          name: 'test_check',
          email: email,
          phone: '0000000000',
          file_id: 0
        })
      });

      console.log('Email 檢查 API 響應狀態:', response.status);
      const data = await response.json().catch(() => ({}));
      console.log('Email 檢查 API 響應數據:', data);

      if (response.status === 400 && data.message && data.message.includes('已經註冊')) {
        resultDiv.innerHTML = `
          <div class="text-red-500">
            <p>❌ Email 已存在於外部 API 數據庫</p>
            <p>狀態碼: ${response.status}</p>
            <p>錯誤訊息: ${data.message}</p>
            <p>建議: 使用不同的 Email 或聯繫管理員刪除此 Email</p>
          </div>
        `;
      } else if (response.ok || (response.status !== 400)) {
        resultDiv.innerHTML = `
          <div class="text-green-500">
            <p>✅ Email 不存在於外部 API 數據庫</p>
            <p>狀態碼: ${response.status}</p>
            <p>回應: ${JSON.stringify(data, null, 2)}</p>
            <p>建議: 可以使用此 Email 進行註冊</p>
          </div>
        `;
      } else {
        resultDiv.innerHTML = `
          <div class="text-yellow-500">
            <p>⚠️ 無法確定 Email 狀態</p>
            <p>狀態碼: ${response.status}</p>
            <p>回應: ${JSON.stringify(data, null, 2)}</p>
          </div>
        `;
      }
    } catch (error) {
      console.error('Email 檢查測試錯誤:', error);
      resultDiv.innerHTML = `<div class="text-red-500">❌ 錯誤: ${error.message}</div>`;
    }
  }

  async function testCheckCode() {
    const email = document.getElementById('test-verify-email').value.trim();
    const code = document.getElementById('test-verify-code').value.trim();
    const resultDiv = document.getElementById('verify-result');
    
    if (!email || !code) {
      resultDiv.innerHTML = '<div class="text-red-500">請填寫 Email 和驗證碼</div>';
      return;
    }

    resultDiv.innerHTML = '<div class="text-blue-500">檢查中...</div>';

    try {
      console.log('=== 測試檢查驗證碼 API ===');
      console.log('Email:', email);
      console.log('驗證碼:', code);

      const response = await fetch(`${API_BASE}/auth/check_mail_code?loginMail=${encodeURIComponent(email)}&verifyCode=${encodeURIComponent(code)}`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json'
        }
      });

      console.log('檢查驗證碼 API 響應狀態:', response.status);
      const data = await response.json().catch(() => ({}));
      console.log('檢查驗證碼 API 響應數據:', data);

      if (response.ok && (data.success === true || data.code === 20000)) {
        resultDiv.innerHTML = `
          <div class="text-green-500">
            <p>✅ 驗證碼正確！</p>
            <p>訊息: ${data.message || '驗證成功'}</p>
          </div>
        `;
      } else {
        resultDiv.innerHTML = `
          <div class="text-red-500">
            <p>❌ 驗證失敗</p>
            <p>狀態碼: ${response.status}</p>
            <p>錯誤訊息: ${data.message || '未知錯誤'}</p>
          </div>
        `;
      }
    } catch (error) {
      console.error('檢查驗證碼測試錯誤:', error);
      resultDiv.innerHTML = `<div class="text-red-500">❌ 錯誤: ${error.message}</div>`;
    }
  }
  </script>
</body>
</html>
