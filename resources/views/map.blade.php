<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>OpenStreetMap 地圖定位系統</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    html, body { height: 100%; margin: 0; }
    body { overflow: hidden; }
    #map { height: 80vh; }
    
    /* 頁面頂部按鈕區域樣式 */
    .header-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      margin-bottom: 20px;
      border-bottom: 2px solid #e5e5e5;
    }
    
    .header-left h2 {
      margin: 0;
      color: #333;
    }
    
    .header-right {
      display: flex;
      gap: 10px;
    }
    
    .control-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    
    .control-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .btn-user-info {
      background-color: #17a2b8;
      color: white;
    }
    
    .btn-user-info:hover {
      background-color: #138496;
      color: white;
    }
    
    .btn-change-password {
      background-color: #ffc107;
      color: #212529;
    }
    
    .btn-change-password:hover {
      background-color: #e0a800;
    }
    
    /* 新增註冊按鈕樣式 */
    .btn-register {
      background-color: #28a745;
      color: white;
    }
    
    .btn-register:hover {
      background-color: #218838;
      color: white;
    }
    
    .btn-logout {
      background-color: #dc3545;
      color: white;
    }
    
    .btn-logout:hover {
      background-color: #c82333;
    }
    
    /* Modal 樣式 */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: none;
      border-radius: 10px;
      width: 80%;
      max-width: 500px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e5e5e5;
    }
    
    .modal-title {
      margin: 0;
      color: #333;
    }
    
    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      background: none;
      border: none;
    }
    
    .close:hover,
    .close:focus {
      color: #000;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #555;
    }
    
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      box-sizing: border-box;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }
    
    .form-group textarea {
      resize: vertical;
      min-height: 60px;
    }
    
    .btn-submit {
      background-color: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      width: 100%;
    }
    
    .btn-submit:hover {
      background-color: #0056b3;
    }
    
    .btn-submit:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }
    
    .user-info-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .user-info-table td {
      padding: 8px;
      border-bottom: 1px solid #eee;
    }
    
    .user-info-table td:first-child {
      font-weight: bold;
      color: #555;
      width: 30%;
    }
    
    .alert {
      padding: 10px;
      margin: 10px 0;
      border-radius: 4px;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .form-row {
      display: flex;
      gap: 10px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .required {
      color: red;
    }
  </style>
</head>
<body>
  <!-- 頁面頂部控制區域 -->
  <div class="header-controls">
    <div class="header-left">
      <h2>地圖自動顯示目前位置</h2>
    </div>
    <div class="header-right">
      <button onclick="showUserInfo()" class="control-btn btn-user-info">
        👤 查看用戶資料
      </button>
      <button onclick="showChangePassword()" class="control-btn btn-change-password">
        🔒 更改密碼
      </button>
      <!-- 新增註冊按鈕 -->
      <button onclick="showRegister()" class="control-btn btn-register">
        📝 註冊新用戶
      </button>
      <button onclick="logout()" class="control-btn btn-logout">
        🚪 登出
      </button>
    </div>
  </div>

  <!-- 地圖容器 -->
  <div id="map"></div>

  <!-- 用戶資料 Modal -->
  <div id="userInfoModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">用戶資料</h3>
        <button class="close" onclick="closeModal('userInfoModal')">&times;</button>
      </div>
      <div id="userInfoContent">
        <p>載入中...</p>
      </div>
    </div>
  </div>

  <!-- 更改密碼 Modal -->
  <div id="changePasswordModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">更改密碼</h3>
        <button class="close" onclick="closeModal('changePasswordModal')">&times;</button>
      </div>
      <form id="passwordForm">
        <div id="passwordAlert"></div>
        <div class="form-group">
          <label for="oldPassword">舊密碼:</label>
          <input type="password" id="oldPassword" name="oldPassword" required>
        </div>
        <div class="form-group">
          <label for="newPassword">新密碼:</label>
          <input type="password" id="newPassword" name="password" required>
        </div>
        <button type="submit" class="btn-submit">更新密碼</button>
      </form>
    </div>
  </div>

  <!-- 註冊用戶 Modal -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">註冊新用戶</h3>
        <button class="close" onclick="closeModal('registerModal')">&times;</button>
      </div>
      <form id="registerForm">
        <div id="registerAlert"></div>
        
        <div class="form-group">
          <label for="regAccount">帳號（建議用 Email）:</label>
          <input type="text" id="regAccount" name="account" placeholder="請輸入帳號">
          <small style="color: #666; font-size: 12px;">目前後端未使用此欄位，若要作為登入帳號可再調整。</small>
        </div>

        <div class="form-group">
          <label for="regName">姓名 <span class="required">*</span>:</label>
          <input type="text" id="regName" name="name" required placeholder="請輸入姓名">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="regBirthday">生日:</label>
            <input type="date" id="regBirthday" name="birthday">
          </div>
          <div class="form-group">
            <label for="regPhone">手機:</label>
            <input type="tel" id="regPhone" name="phone" placeholder="請輸入手機號碼">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="regEmail">Email <span class="required">*</span>:</label>
            <input type="email" id="regEmail" name="email" required placeholder="請輸入電子郵件">
          </div>

        <div class="form-row">
          <div class="form-group">
            <label for="regPassword">密碼 <span class="required">*</span>:</label>
            <input type="password" id="regPassword" name="password" required placeholder="請輸入密碼">
          </div>
          <div class="form-group">
            <label for="regPasswordConfirm">確認密碼 <span class="required">*</span>:</label>
            <input type="password" id="regPasswordConfirm" name="password_confirmation" required placeholder="再次輸入密碼">
          </div>
        </div>

        <button type="submit" class="btn-submit">建立帳號</button>
      </form>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // 全域變數
    let map;
    let csrfToken = '';
    let userLocationMarker = null;

    // 初始化 CSRF Token
    function initializeCSRFToken() {
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
      } else {
        console.warn('CSRF token not found');
      }
    }

    function resizeMapContainer() {
      const header = document.querySelector('.header-controls');
      const headerHeight = header ? header.offsetHeight : 0;
      const mapEl = document.getElementById('map');
      if (mapEl) {
        mapEl.style.height = Math.max(200, window.innerHeight - headerHeight) + 'px';
      }
      if (typeof map !== 'undefined' && map) {
        setTimeout(() => map.invalidateSize(), 0);
      }
    }
    // 初始化地圖
    function initializeMap() {
      // 建立地圖實例
      map = L.map('map').setView([23.5, 121], 7); // 預設台灣中心

      // 添加圖磚層
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OSM</a> 貢獻者'
      }).addTo(map);

      // 載入後端標記
      loadMapMarkers();

      // 自動定位
      getCurrentLocation();
    }

    // 載入地圖標記
    function loadMapMarkers() {
      fetch('/map/markers')
        .then(response => {
          if (!response.ok) {
            throw new Error('無法載入地圖標記');
          }
          return response.json();
        })
        .then(data => {
          if (Array.isArray(data)) {
            data.forEach(marker => {
              L.marker([marker.lat, marker.lng])
                .addTo(map)
                .bindPopup(marker.name);
            });
          }
        })
        .catch(error => {
          console.error('載入地圖標記失敗:', error);
        });
    }

    // 獲取當前位置
    function getCurrentLocation() {
      if (!navigator.geolocation) {
        alert('您的瀏覽器不支援地理定位功能');
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          // 移動地圖到當前位置
          map.setView([lat, lng], 16);

          // 如果已經有位置標記，先移除
          if (userLocationMarker) {
            map.removeLayer(userLocationMarker);
          }

          // 添加定位圓圈
          L.circle([lat, lng], {
            radius: 30,
            color: '#3f9bff',
            fillColor: '#3f9bff',
            fillOpacity: 0.2,
            weight: 1
          }).addTo(map);

          // 添加用戶位置標記
          userLocationMarker = L.circleMarker([lat, lng], {
            radius: 8,
            color: '#136AEC',
            fillColor: '#2A93EE',
            fillOpacity: 1,
            weight: 2
          }).addTo(map).bindPopup("你目前的位置").openPopup();
        },
        (error) => {
          let errorMessage = '定位失敗: ';
          switch(error.code) {
            case error.PERMISSION_DENIED:
              errorMessage += '用戶拒絕了定位請求';
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage += '位置信息不可用';
              break;
            case error.TIMEOUT:
              errorMessage += '定位請求超時';
              break;
            default:
              errorMessage += '發生未知錯誤';
              break;
          }
          alert(errorMessage);
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 60000
        }
      );
    }

    // 顯示用戶資料 Modal
    async function showUserInfo() {
      try {
        document.getElementById('userInfoModal').style.display = 'block';
        document.getElementById('userInfoContent').innerHTML = '<p>載入中...</p>';
        
        const response = await fetch('/user/info', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        });

        if (!response.ok) {
          throw new Error('無法載入用戶資料');
        }

        const data = await response.json();
        
        let userInfoHtml = '';
        if (data.success && data.data) {
          const user = data.data;
          userInfoHtml = `
            <table class="user-info-table">
            <tr><td>用戶ID:</td><td>${user.id || 'N/A'}</td></tr>
            <tr><td>帳號:</td><td>${user.account || 'N/A'}</td></tr>
            <tr><td>姓名:</td><td>${user.name || 'N/A'}</td></tr>
            <tr><td>電子郵件:</td><td>${user.email || 'N/A'}</td></tr>
            <tr><td>手機:</td><td>${user.phone || 'N/A'}</td></tr>
            <tr><td>角色名稱:</td><td>${user.role_name || 'N/A'}</td></tr>
            <tr><td>角色代碼:</td><td>${user.role_code || 'N/A'}</td></tr>
            <tr><td>建立時間:</td><td>${user.create_time || 'N/A'}</td></tr>
            <tr><td>修改時間:</td><td>${user.modify_time || 'N/A'}</td></tr>
            </table>
          `;
        } else {
          userInfoHtml = '<p>無法載入用戶資料</p>';
        }

        document.getElementById('userInfoContent').innerHTML = userInfoHtml;
      } catch (error) {
        document.getElementById('userInfoContent').innerHTML = '<p>載入用戶資料時發生錯誤</p>';
        console.error('Error loading user info:', error);
      }
    }

    // 顯示更改密碼 Modal
    function showChangePassword() {
      document.getElementById('changePasswordModal').style.display = 'block';
      document.getElementById('passwordForm').reset();
      document.getElementById('passwordAlert').innerHTML = '';
    }

    // 顯示註冊 Modal
    function showRegister() {
      document.getElementById('registerModal').style.display = 'block';
      document.getElementById('registerForm').reset();
      document.getElementById('registerAlert').innerHTML = '';
    }

    // 關閉 Modal
    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    // 處理更改密碼表單
    function handlePasswordForm() {
      document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const oldPassword = document.getElementById('oldPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const alertDiv = document.getElementById('passwordAlert');
        
        // 清除之前的提示
        alertDiv.innerHTML = '';
        
        // 驗證密碼
        if (newPassword.length < 6) {
          alertDiv.innerHTML = '<div class="alert alert-error">新密碼至少需要6個字元！</div>';
          return;
        }

        try {
          const submitBtn = document.querySelector('#passwordForm .btn-submit');
          submitBtn.disabled = true;
          submitBtn.textContent = '更新中...';

          // 檢查是否有 CSRF token
          if (!csrfToken) {
            alertDiv.innerHTML = '<div class="alert alert-error">安全驗證失敗，請重新整理頁面</div>';
            return;
          }

          const response = await fetch('/user/update_pwd', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              oldPassword: oldPassword,
              password: newPassword
            })
          });

          let data = {};
          try {
            data = await response.json();
          } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            data = { message: '伺服器回應格式錯誤' };
          }

          if (response.ok && data.success) {
            alertDiv.innerHTML = '<div class="alert alert-success">密碼更新成功！</div>';
            document.getElementById('passwordForm').reset();
            setTimeout(() => {
              closeModal('changePasswordModal');
            }, 2000);
          } else {
            let errorMessage = '更新密碼失敗';
            
            if (response.status === 401) {
              errorMessage = '身份驗證失敗，請重新登入';
            } else if (response.status === 422) {
              errorMessage = '密碼格式不正確或舊密碼錯誤';
            } else if (data.message) {
              errorMessage = data.message;
            }
            
            alertDiv.innerHTML = `<div class="alert alert-error">${errorMessage}</div>`;
          }
        } catch (error) {
          console.error('Error changing password:', error);
          alertDiv.innerHTML = '<div class="alert alert-error">網路連線錯誤，請檢查網路狀態</div>';
        } finally {
          const submitBtn = document.querySelector('#passwordForm .btn-submit');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = '更新密碼';
          }
        }
      });
    }

    // 處理註冊表單
    function handleRegisterForm() {
      document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const alertDiv = document.getElementById('registerAlert');
        
        // 清除之前的提示
        alertDiv.innerHTML = '';
        
        // 驗證必填欄位
        if (!data.name || !data.email || !data.password || !data.password_confirmation) {
          alertDiv.innerHTML = '<div class="alert alert-error">請填寫所有必填欄位！</div>';
          return;
        }
        
        // 驗證密碼
        if (data.password !== data.password_confirmation) {
          alertDiv.innerHTML = '<div class="alert alert-error">密碼與確認密碼不符！</div>';
          return;
        }
        
        if (data.password.length < 6) {
          alertDiv.innerHTML = '<div class="alert alert-error">密碼至少需要6個字元！</div>';
          return;
        }

        try {
          const submitBtn = document.querySelector('#registerForm .btn-submit');
          submitBtn.disabled = true;
          submitBtn.textContent = '註冊中...';

          const response = await fetch('/register', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
          });

          let responseData = {};
          try {
            responseData = await response.json();
          } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            responseData = { message: '伺服器回應格式錯誤' };
          }

          if (response.ok) {
            alertDiv.innerHTML = '<div class="alert alert-success">註冊成功！即將跳轉...</div>';
            document.getElementById('registerForm').reset();
            setTimeout(() => {
              closeModal('registerModal');
              // 可選擇跳轉到登入頁面或其他頁面
              // window.location.href = '/login';
            }, 2000);
          } else {
            let errorMessage = '註冊失敗';
            
            if (response.status === 422 && responseData.errors) {
              // Laravel 驗證錯誤
              const errors = Object.values(responseData.errors).flat();
              errorMessage = errors.join(', ');
            } else if (responseData.message) {
              errorMessage = responseData.message;
            }
            
            alertDiv.innerHTML = `<div class="alert alert-error">${errorMessage}</div>`;
          }
        } catch (error) {
          console.error('Error during registration:', error);
          alertDiv.innerHTML = '<div class="alert alert-error">網路連線錯誤，請檢查網路狀態</div>';
        } finally {
          const submitBtn = document.querySelector('#registerForm .btn-submit');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = '建立帳號';
          }
        }
      });
    }

    // 登出功能
    async function logout() {
      if (confirm('確定要登出嗎？')) {
        try {
          const response = await fetch('/logout', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken
            }
          });

          // 無論 response 如何，都重定向到登入頁面
          window.location.href = '/login';
        } catch (error) {
          console.error('Logout error:', error);
          // 即使發生錯誤也重定向到登入頁面
          window.location.href = '/login';
        }
      }
    }

    // 點擊 Modal 外部關閉
    window.onclick = function(event) {
      const userModal = document.getElementById('userInfoModal');
      const passwordModal = document.getElementById('changePasswordModal');
      const registerModal = document.getElementById('registerModal');
      
      if (event.target == userModal) {
        userModal.style.display = 'none';
      }
      if (event.target == passwordModal) {
        passwordModal.style.display = 'none';
      }
      if (event.target == registerModal) {
        registerModal.style.display = 'none';
      }
    }

    // 頁面載入完成後初始化
    document.addEventListener('DOMContentLoaded', function() {
      initializeCSRFToken();
      resizeMapContainer();
      initializeMap();
      handlePasswordForm();
      handleRegisterForm();
    });

    // 視窗大小改變時重新調整地圖容器高度
    window.addEventListener('resize', resizeMapContainer);
  </script>
</body>
</html>