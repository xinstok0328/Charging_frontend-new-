<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>認證測試</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>認證狀態測試</h1>
    
    <div id="auth-status">
        <p>載入中...</p>
    </div>
    
    <div>
        <button onclick="testUserInfo()">測試 /user/info</button>
        <button onclick="testUpdateProfile()">測試 /user/update_profile</button>
    </div>
    
    <div id="test-results"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        async function testUserInfo() {
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = '<p>測試 /user/info...</p>';
            
            try {
                const response = await fetch('/user/info', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                resultsDiv.innerHTML = `
                    <h3>/user/info 結果:</h3>
                    <p>狀態碼: ${response.status}</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<p>錯誤: ${error.message}</p>`;
            }
        }
        
        async function testUpdateProfile() {
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = '<p>測試 /user/update_profile...</p>';
            
            try {
                const response = await fetch('/user/update_profile', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        name: '測試用戶',
                        email: 'test@example.com',
                        phone: '0912345678',
                        file_id: 1
                    })
                });
                
                const data = await response.json();
                resultsDiv.innerHTML = `
                    <h3>/user/update_profile 結果:</h3>
                    <p>狀態碼: ${response.status}</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<p>錯誤: ${error.message}</p>`;
            }
        }
        
        // 頁面載入時檢查認證狀態
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('/auth/status', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                document.getElementById('auth-status').innerHTML = `
                    <h3>當前認證狀態:</h3>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                document.getElementById('auth-status').innerHTML = `<p>無法獲取認證狀態: ${error.message}</p>`;
            }
        });
    </script>
</body>
</html>
