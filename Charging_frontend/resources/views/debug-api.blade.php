<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>API 除錯</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">API 除錯工具</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 步驟 1: 檢查 CSRF Token -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">步驟 1: 檢查 CSRF Token</h2>
                <button id="checkCsrfBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    檢查 CSRF Token
                </button>
                <div id="csrfResult" class="mt-4 p-4 bg-gray-100 rounded hidden">
                    <pre id="csrfInfo" class="text-sm"></pre>
                </div>
            </div>

            <!-- 步驟 2: 測試簡單 POST -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">步驟 2: 測試簡單 POST</h2>
                <button id="testSimplePostBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    測試簡單 POST
                </button>
                <div id="simplePostResult" class="mt-4 p-4 bg-gray-100 rounded hidden">
                    <pre id="simplePostInfo" class="text-sm"></pre>
                </div>
            </div>

            <!-- 步驟 3: 測試充電 API -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">步驟 3: 測試充電 API</h2>
                <button id="testChargingApiBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                    測試充電 API
                </button>
                <div id="chargingApiResult" class="mt-4 p-4 bg-gray-100 rounded hidden">
                    <pre id="chargingApiInfo" class="text-sm"></pre>
                </div>
            </div>

            <!-- 步驟 4: 檢查 Session -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">步驟 4: 檢查 Session</h2>
                <button id="checkSessionBtn" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                    檢查 Session
                </button>
                <div id="sessionResult" class="mt-4 p-4 bg-gray-100 rounded hidden">
                    <pre id="sessionInfo" class="text-sm"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 步驟 1: 檢查 CSRF Token
        document.getElementById('checkCsrfBtn').addEventListener('click', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const result = {
                csrfToken: csrfToken,
                hasToken: !!csrfToken,
                tokenLength: csrfToken ? csrfToken.length : 0
            };
            
            document.getElementById('csrfResult').classList.remove('hidden');
            document.getElementById('csrfInfo').textContent = JSON.stringify(result, null, 2);
            console.log('CSRF Token 檢查:', result);
        });

        // 步驟 2: 測試簡單 POST
        document.getElementById('testSimplePostBtn').addEventListener('click', async () => {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }

                console.log('測試簡單 POST 請求');
                const resp = await fetch('/debug/post-test', {
                    method: 'POST',
                    headers,
                    credentials: 'same-origin',
                    body: JSON.stringify({ test: 'data', timestamp: new Date().toISOString() })
                });

                const result = {
                    status: resp.status,
                    statusText: resp.statusText,
                    ok: resp.ok,
                    body: await resp.text()
                };

                document.getElementById('simplePostResult').classList.remove('hidden');
                document.getElementById('simplePostInfo').textContent = JSON.stringify(result, null, 2);
                console.log('簡單 POST 測試結果:', result);
                
            } catch (error) {
                document.getElementById('simplePostResult').classList.remove('hidden');
                document.getElementById('simplePostInfo').textContent = '錯誤: ' + error.message;
                console.error('簡單 POST 測試錯誤:', error);
            }
        });

        // 步驟 3: 測試充電 API
        document.getElementById('testChargingApiBtn').addEventListener('click', async () => {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }

                // 先檢查 localStorage 是否有預約資料
                const existingReservation = localStorage.getItem('activeReservation');
                let requestData;
                
                // 根據後端 API 文檔，使用空 body 讓後端從 Token 識別用戶
                console.log('測試充電 API 請求（空 body）');
                const resp = await fetch('/user/purchase/start', {
                    method: 'POST',
                    headers,
                    credentials: 'same-origin',
                    body: '' // 空 body，依賴 token 識別用戶
                });

                const result = {
                    status: resp.status,
                    statusText: resp.statusText,
                    ok: resp.ok,
                    headers: Object.fromEntries(resp.headers.entries()),
                    body: await resp.text()
                };

                document.getElementById('chargingApiResult').classList.remove('hidden');
                document.getElementById('chargingApiInfo').textContent = JSON.stringify(result, null, 2);
                console.log('充電 API 測試結果:', result);
                
            } catch (error) {
                document.getElementById('chargingApiResult').classList.remove('hidden');
                document.getElementById('chargingApiInfo').textContent = '錯誤: ' + error.message;
                console.error('充電 API 測試錯誤:', error);
            }
        });

        // 步驟 4: 檢查 Session
        document.getElementById('checkSessionBtn').addEventListener('click', async () => {
            try {
                const resp = await fetch('/debug/session', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                
                const data = await resp.json();
                document.getElementById('sessionResult').classList.remove('hidden');
                document.getElementById('sessionInfo').textContent = JSON.stringify(data, null, 2);
                console.log('Session 資訊:', data);
                
            } catch (error) {
                document.getElementById('sessionResult').classList.remove('hidden');
                document.getElementById('sessionInfo').textContent = '錯誤: ' + error.message;
                console.error('Session 檢查錯誤:', error);
            }
        });
    </script>
</body>
</html>
