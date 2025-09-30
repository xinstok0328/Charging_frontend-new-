<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入測試</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>登入測試</h1>
    
    <form id="loginForm">
        <div>
            <label for="account">帳號:</label>
            <input type="text" id="account" name="account" required>
        </div>
        <div>
            <label for="password">密碼:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">登入</button>
    </form>
    
    <div id="loginResult"></div>
    
    <hr>
    
    <div>
        <button onclick="checkAuthStatus()">檢查認證狀態</button>
        <button onclick="testUpdateProfile()">測試更新資料</button>
    </div>
    
    <div id="testResults"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const resultDiv = document.getElementById('loginResult');
            
            try {
                const response = await fetch('/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                resultDiv.innerHTML = `
                    <h3>登入結果:</h3>
                    <p>狀態碼: ${response.status}</p>
                    <pre>${JSON.stringify(result, null, 2)}</pre>
                `;
                
                if (response.ok && result.success) {
                    setTimeout(() => {
                        checkAuthStatus();
                    }, 1000);
                }
            } catch (error) {
                resultDiv.innerHTML = `<p>錯誤: ${error.message}</p>`;
            }
        });
        
        async function checkAuthStatus() {
            const resultsDiv = document.getElementById('testResults');
            resultsDiv.innerHTML = '<p>檢查認證狀態...</p>';
            
            try {
                const response = await fetch('/debug/auth-status', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                resultsDiv.innerHTML = `
                    <h3>認證狀態:</h3>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<p>錯誤: ${error.message}</p>`;
            }
        }
        
        async function testUpdateProfile() {
            const resultsDiv = document.getElementById('testResults');
            resultsDiv.innerHTML = '<p>測試更新資料...</p>';
            
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
                    <h3>更新結果:</h3>
                    <p>狀態碼: ${response.status}</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                resultsDiv.innerHTML = `<p>錯誤: ${error.message}</p>`;
            }
        }
        
        // 頁面載入時檢查認證狀態
        document.addEventListener('DOMContentLoaded', function() {
            checkAuthStatus();
        });
    </script>
</body>
</html>
