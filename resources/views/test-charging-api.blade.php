<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>測試充電 API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">測試充電 API</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">API 測試</h2>
            <button id="testApiBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                測試 /user/purchase/start
            </button>
            <div id="apiResult" class="mt-4 p-4 bg-gray-100 rounded hidden">
                <h3 class="font-semibold">API 回應：</h3>
                <pre id="apiResponse" class="mt-2 text-sm"></pre>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Session 資訊</h2>
            <button id="checkSessionBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                檢查 Session
            </button>
            <div id="sessionResult" class="mt-4 p-4 bg-gray-100 rounded hidden">
                <h3 class="font-semibold">Session 資訊：</h3>
                <pre id="sessionInfo" class="mt-2 text-sm"></pre>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('testApiBtn').addEventListener('click', async () => {
            const resultDiv = document.getElementById('apiResult');
            const responseDiv = document.getElementById('apiResponse');
            
            resultDiv.classList.remove('hidden');
            responseDiv.textContent = '測試中...';
            
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

                // 根據後端 API 文檔，使用空 body 讓後端從 Token 識別用戶
                console.log('發送空 body 請求到 /user/purchase/start');
                const resp = await fetch('/user/purchase/start', {
                    method: 'POST',
                    headers,
                    credentials: 'same-origin',
                    body: '' // 空 body，依賴 token 識別用戶
                });

                const responseData = {
                    status: resp.status,
                    statusText: resp.statusText,
                    headers: Object.fromEntries(resp.headers.entries()),
                    body: await resp.text()
                };

                responseDiv.textContent = JSON.stringify(responseData, null, 2);
                console.log('API 測試結果:', responseData);
                
            } catch (error) {
                responseDiv.textContent = '錯誤: ' + error.message;
                console.error('API 測試錯誤:', error);
            }
        });

        document.getElementById('checkSessionBtn').addEventListener('click', async () => {
            const resultDiv = document.getElementById('sessionResult');
            const infoDiv = document.getElementById('sessionInfo');
            
            resultDiv.classList.remove('hidden');
            infoDiv.textContent = '檢查中...';
            
            try {
                const resp = await fetch('/debug/session', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                
                const data = await resp.json();
                infoDiv.textContent = JSON.stringify(data, null, 2);
                console.log('Session 資訊:', data);
                
            } catch (error) {
                infoDiv.textContent = '錯誤: ' + error.message;
                console.error('Session 檢查錯誤:', error);
            }
        });
    </script>
</body>
</html>
