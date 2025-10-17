<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Email 生成器</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
  <div class="w-full max-w-md bg-white rounded-2xl shadow p-8">
    <h1 class="text-2xl font-semibold text-center mb-6">Email 生成器</h1>
    <p class="text-sm text-gray-600 text-center mb-6">生成一個全新的 email 用於註冊測試</p>
    
    <div class="space-y-4">
      <div>
        <label for="usernameInput" class="block text-sm font-medium text-gray-700 mb-2">用戶名</label>
        <input type="text" id="usernameInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="輸入用戶名">
      </div>
      
      <div>
        <label for="domainSelect" class="block text-sm font-medium text-gray-700 mb-2">域名</label>
        <select id="domainSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="gmail.com">gmail.com</option>
          <option value="yahoo.com">yahoo.com</option>
          <option value="hotmail.com">hotmail.com</option>
          <option value="outlook.com">outlook.com</option>
          <option value="test.com">test.com</option>
        </select>
      </div>
      
      <div>
        <label for="generatedEmail" class="block text-sm font-medium text-gray-700 mb-2">生成的 Email</label>
        <div class="flex">
          <input type="text" id="generatedEmail" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
          <button onclick="copyEmail()" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
            複製
          </button>
        </div>
      </div>
      
      <div class="flex gap-2">
        <button onclick="generateEmail()" class="flex-1 bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition-colors">
          生成新 Email
        </button>
        <button onclick="generateRandomEmail()" class="flex-1 bg-purple-500 text-white py-2 px-4 rounded-lg hover:bg-purple-600 transition-colors">
          隨機生成
        </button>
      </div>
      
      <div class="text-center">
        <a href="/register" class="text-blue-500 hover:text-blue-700 underline">前往註冊頁面</a>
      </div>
    </div>
  </div>

  <script>
    function generateEmail() {
      const username = document.getElementById('usernameInput').value;
      const domain = document.getElementById('domainSelect').value;
      const email = `${username}@${domain}`;
      document.getElementById('generatedEmail').value = email;
    }
    
    function generateRandomEmail() {
      const timestamp = Date.now().toString().slice(-8);
      const randomNum = Math.floor(Math.random() * 10000);
      const randomUsername = 'user' + timestamp + randomNum;
      document.getElementById('usernameInput').value = randomUsername;
      generateEmail();
    }
    
    function copyEmail() {
      const emailInput = document.getElementById('generatedEmail');
      emailInput.select();
      document.execCommand('copy');
      
      // 顯示複製成功提示
      const button = event.target;
      const originalText = button.textContent;
      button.textContent = '已複製!';
      button.classList.add('bg-green-500');
      button.classList.remove('bg-blue-500');
      
      setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('bg-green-500');
        button.classList.add('bg-blue-500');
      }, 2000);
    }
    
    // 頁面載入時自動生成一個隨機 email
    window.onload = function() {
      generateRandomEmail();
    };
  </script>
</body>
</html>