<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>充電站地圖定位系統</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    html, body { height: 100%; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    body { overflow: hidden; background-color: #f5f5f5; }
    #map { height: 80vh; }
    
    /* 動態訊息框樣式 */
    .message-container {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1001;
      padding: 12px 20px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transform: translateY(-100%);
      transition: transform 0.3s ease-in-out;
      color: white;
    }

    .message-container.show {
      transform: translateY(0);
    }

    .message-container.error {
      background: linear-gradient(135deg, #ff7b7b 0%, #d63031 100%);
    }

    .message-container.success {
      background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    }

    .message-content {
      max-width: 1200px;
      margin: 0 auto;
    }

    .message-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .message-title {
      font-size: 16px;
      font-weight: bold;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .message-status {
      font-size: 12px;
      opacity: 0.9;
    }

    .message-close-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
    }

    .message-close-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .data-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 12px;
      margin-top: 8px;
    }

    .data-item {
      background: rgba(255, 255, 255, 0.1);
      padding: 8px 10px;
      border-radius: 6px;
      backdrop-filter: blur(10px);
    }

    .data-label {
      font-size: 11px;
      opacity: 0.8;
      margin-bottom: 2px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .data-value {
      font-size: 14px;
      font-weight: bold;
    }

    .currency-value {
      color: #ffeaa7;
    }

    .datetime-value {
      font-size: 12px;
      font-family: 'Courier New', monospace;
    }

    .update-indicator {
      display: inline-block;
      width: 6px;
      height: 6px;
      background-color: #00b894;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.5; }
      100% { opacity: 1; }
    }

    /* 調整其他元素位置，為訊息框留空間 */
    body.message-shown {
      padding-top: 120px;
    }
    
    /* 頁面頂部按鈕區域樣式 - 調整 z-index */
    .header-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
      margin-bottom: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: relative;
      z-index: 1000;
    }
    
    .header-left h2 {
      margin: 0;
      color: white;
      font-size: 1.5em;
    }
    
    .header-right {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
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
      font-weight: 600;
    }
    
    .control-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    .btn-user-info {
      background-color: #17a2b8;
      color: white;
    }
    
    .btn-user-info:hover {
      background-color: #138496;
      color: white;
    }
    
    .btn-update-profile {
      background-color: #6f42c1;
      color: white;
    }
    
    .btn-update-profile:hover {
      background-color: #5a32a3;
      color: white;
    }
    
    .btn-change-password {
      background-color: #ffc107;
      color: #212529;
    }
    
    .btn-change-password:hover {
      background-color: #e0a800;
    }
    
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

    /* 新增費率控制按鈕 */
    .btn-rate-info {
      background-color: #6f42c1;
      color: white;
    }
    
    .btn-rate-info:hover {
      background-color: #5a2a87;
    }

    /* 充電站控制區域 */
    .station-controls {
      background: #f8f9fa;
      padding: 15px 20px;
      border-bottom: 1px solid #dee2e6;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
    }
    
    .form-group {
      display: flex;
      flex-direction: column;
      min-width: 150px;
    }
    
    .form-group label {
      font-weight: 600;
      margin-bottom: 5px;
      color: #495057;
      font-size: 14px;
    }
    
    .form-group input {
      padding: 8px 12px;
      border: 1px solid #ced4da;
      border-radius: 5px;
      font-size: 14px;
      transition: border-color 0.3s;
    }
    
    .form-group input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    }
    
    .button-group {
      display: flex;
      gap: 10px;
      align-items: flex-end;
      flex-wrap: wrap;
    }
    
    .station-btn {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .station-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .station-btn.secondary {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }
    
    /* 狀態列 */
    .status-bar {
      padding: 10px 20px;
      background: #e9ecef;
      font-size: 14px;
      color: #6c757d;
      border-top: 1px solid #dee2e6;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    
    .status-item {
      margin-right: 20px;
    }
    
    /* 載入和錯誤提示 */
    .loading {
      display: none;
      text-align: center;
      padding: 20px;
      color: #667eea;
      background: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
    }
    
    .loading.show {
      display: block;
    }
    
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 10px 20px;
      border-left: 4px solid #dc3545;
      margin: 0;
      display: none;
    }
    
    .error-message.show {
      display: block;
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
      margin: 5% auto;
      padding: 20px;
      border: none;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      max-height: 90vh;
      overflow-y: auto;
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
    
    .btn-cancel {
      background-color: #e9ecef;
      color: #333;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      width: 100%;
    }

    .btn-cancel:hover {
      background-color: #dde2e6;
    }

    .form-actions {
      display: flex;
      gap: 10px;
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

    /* 標記彈出視窗樣式 */
    .marker-popup {
      min-width: 200px;
    }
    
    .marker-popup h4 {
      margin-top: 0;
      color: #333;
      border-bottom: 2px solid #667eea;
      padding-bottom: 5px;
    }
    
    .marker-popup p {
      margin: 8px 0;
      font-size: 14px;
    }

    /* 響應式設計 */
    @media (max-width: 768px) {
      .header-controls {
        flex-direction: column;
        gap: 10px;
        text-align: center;
      }
      
      .station-controls {
        flex-direction: column;
        align-items: stretch;
      }
      
      .form-group {
        min-width: unset;
      }
      
      .button-group {
        justify-content: center;
      }
      
      #map {
        height: 70vh;
      }
      
      .status-bar {
        flex-direction: column;
        gap: 5px;
      }
      
      .status-item {
        margin-right: 0;
      }

      .data-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 8px;
      }

      .message-container {
        padding: 8px 15px;
      }

      body.message-shown {
        padding-top: 100px;
      }
    }
  </style>
</head>
<body>
  <!-- 動態訊息框 -->
  <div class="message-container" id="messageBox">
    <div class="message-content">
      <div class="message-header">
        <h3 class="message-title">
          充電費率資訊
          <span class="update-indicator"></span>
        </h3>
        <div style="display: flex; align-items: center; gap: 15px;">
          <span class="message-status" id="messageStatus">正在更新...</span>
          <button class="message-close-btn" onclick="hideRateMessage()">隱藏</button>
        </div>
      </div>
      
      <div class="data-grid">
        <div class="data-item">
          <div class="data-label">費率名稱</div>
          <div class="data-value" id="rateName">載入中...</div>
        </div>
        
        <div class="data-item">
          <div class="data-label">每度電價格</div>
          <div class="data-value currency-value" id="pricePerKwh">載入中...</div>
        </div>
        
        <div class="data-item">
          <div class="data-label">時間費用/分鐘</div>
          <div class="data-value currency-value" id="timeFeePerMin">載入中...</div>
        </div>
        
        <div class="data-item">
          <div class="data-label">服務費</div>
          <div class="data-value currency-value" id="serviceFee">載入中...</div>
        </div>
        
        <div class="data-item">
          <div class="data-label">貨幣</div>
          <div class="data-value" id="currency">載入中...</div>
        </div>
        
        <div class="data-item">
          <div class="data-label">生效時間</div>
          <div class="data-value datetime-value" id="effectiveFrom">載入中...</div>
        </div>
        
        <div class="data-item">
          <div class="data-label">失效時間</div>
          <div class="data-value datetime-value" id="effectiveTo">載入中...</div>
        </div>
        
        <div class="data-item">
          <div class="data-label">API 回應碼</div>
          <div class="data-value" id="responseCode">載入中...</div>
        </div>
      </div>
    </div>
  </div>

  <!-- 頁面頂部控制區域 -->
  <div class="header-controls">
    <div class="header-left">
      <h2>充電站地圖定位系統</h2>
    </div>
    <div class="header-right">
      <button onclick="showRateInfo()" class="control-btn btn-rate-info">
        費率資訊
      </button>
      <button onclick="showUserInfo()" class="control-btn btn-user-info">
        用戶資料
      </button>
      <button onclick="showUpdateProfile()" class="control-btn btn-update-profile">
        ✏️ 更新資料
      </button>
      <button onclick="showChangePassword()" class="control-btn btn-change-password">
        更改密碼
      </button>
      <button onclick="showRegister()" class="control-btn btn-register">
        註冊新用戶
      </button>
      <button onclick="logout()" class="control-btn btn-logout">
        登出
      </button>
    </div>
  </div>

  <!-- 充電站控制區域 -->
  <div class="station-controls">
    <div class="form-group">
      <label for="search-distance">搜尋範圍 (公里)</label>
      <input type="number" id="search-distance" value="10" min="1" max="100" placeholder="預設10公里">
    </div>
    
    <div class="form-group">
      <label for="station-id">特定站點ID</label>
      <input type="number" id="station-id" placeholder="可選">
    </div>
    
    <!-- 新增費率查詢參數 -->
    <!-- <div class="form-group">
      <label for="user-id">用戶ID</label>
      <input type="number" id="user-id" placeholder="用於費率查詢">
    </div>
    
    <div class="form-group">
      <label for="user-tier-id">用戶層級ID</label>
      <input type="number" id="user-tier-id" placeholder="用於費率查詢">
    </div>
    
    <div class="form-group">
      <label for="pile-id">充電樁ID</label>
      <input type="number" id="pile-id" placeholder="用於費率查詢">
    </div> -->
    
    <div class="button-group">
      <button onclick="loadNearbyStations()" class="station-btn">載入附近充電站</button>
      <button onclick="loadAllStations()" class="station-btn secondary">載入所有充電站</button>
      <!-- <button onclick="clearMarkers()" class="station-btn secondary">清除標記</button> -->
    </div>
  </div>

  <!-- 錯誤訊息區域 -->
  <div class="error-message" id="error-message"></div>
  
  <!-- 載入提示區域 -->
  <div class="loading" id="loading">
    <p>載入充電站資料中...</p>
  </div>

  <!-- 地圖容器 -->
  <div id="map"></div>

  <!-- 狀態列 -->
  <div class="status-bar">
    <div>
      <span class="status-item" id="marker-count">標記數量: 0</span>
      <span class="status-item" id="user-location">位置: 未取得</span>
    </div>
    <div>
      <span class="status-item" id="last-update">最後更新: --</span>
      <span class="status-item" id="rate-update">費率更新: --</span>
    </div>
  </div>

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

  <!-- 更新資料 Modal -->
  <div id="updateProfileModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">更新會員資料</h3>
        <button class="close" onclick="closeModal('updateProfileModal')">&times;</button>
      </div>
      <form id="updateProfileForm">
        <div id="updateProfileAlert"></div>
        
        <div class="form-group">
          <label for="updateName">姓名:</label>
          <input type="text" id="updateName" name="name" required>
        </div>
        
        <div class="form-group">
          <label for="updateEmail">Email:</label>
          <input type="email" id="updateEmail" name="email" required>
        </div>
        
        <div class="form-group">
          <label for="updatePhone">手機:</label>
          <input type="text" id="updatePhone" name="phone">
        </div>
        
        
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeModal('updateProfileModal')">取消</button>
          <button type="submit" class="btn-submit">更新資料</button>
        </div>
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
          <label for="regAccount">帳號(建議用 Email):</label>
          <input type="text" id="regAccount" name="account" placeholder="請輸入帳號">
          <small style="color: #666; font-size: 12px;">目前後端未使用此欄位,若要作為登入帳號可再調整。</small>
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
    // ✅ 全域變數 - 添加預設座標
    let map;
    let csrfToken = '';
    let userLocationMarker = null;
    let markersGroup;
    let rateUpdateInterval;

    // ✅ 新增：預設座標（台中市中心）
    const DEFAULT_LAT = 24.1477;
    const DEFAULT_LNG = 120.6736;

    // 初始化 CSRF Token
    function initializeCSRFToken() {
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
      } else {
        console.warn('CSRF token not found');
      }
    }

    // 調整地圖容器大小
    function resizeMapContainer() {
      const header = document.querySelector('.header-controls');
      const stationControls = document.querySelector('.station-controls');
      const statusBar = document.querySelector('.status-bar');
      const messageBox = document.querySelector('.message-container');
      const headerHeight = header ? header.offsetHeight : 0;
      const controlsHeight = stationControls ? stationControls.offsetHeight : 0;
      const statusHeight = statusBar ? statusBar.offsetHeight : 0;
      const messageHeight = messageBox && messageBox.classList.contains('show') ? messageBox.offsetHeight : 0;
      const mapEl = document.getElementById('map');
      
      if (mapEl) {
        mapEl.style.height = Math.max(300, window.innerHeight - headerHeight - controlsHeight - statusHeight - messageHeight) + 'px';
      }
      
      if (typeof map !== 'undefined' && map) {
        setTimeout(() => map.invalidateSize(), 0);
      }
    }

    // 顯示錯誤訊息
    function showError(message) {
      const errorEl = document.getElementById('error-message');
      errorEl.textContent = message;
      errorEl.classList.add('show');
      setTimeout(() => errorEl.classList.remove('show'), 5000);
    }

    // 顯示載入狀態
    function showLoading(show = true) {
      const loadingEl = document.getElementById('loading');
      if (show) {
        loadingEl.classList.add('show');
      } else {
        loadingEl.classList.remove('show');
      }
    }

    // 更新狀態列
    function updateStatus(markerCount, userLocation = null) {
      document.getElementById('marker-count').textContent = `標記數量: ${markerCount}`;
      if (userLocation) {
        document.getElementById('user-location').textContent = 
          `位置: ${userLocation.lat.toFixed(4)}, ${userLocation.lng.toFixed(4)}`;
      }
      document.getElementById('last-update').textContent = 
        `最後更新: ${new Date().toLocaleTimeString()}`;
    }

    // 清除所有標記
    function clearMarkers() {
      if (markersGroup) {
        markersGroup.clearLayers();
      }
      if (userLocationMarker) {
        map.removeLayer(userLocationMarker);
        userLocationMarker = null;
      }
      updateStatus(0);
    }

    // === 動態訊息框相關功能 ===
    
    // 顯示費率訊息框
    function showRateInfo() {
      const messageBox = document.getElementById('messageBox');
      messageBox.classList.add('show');
      document.body.classList.add('message-shown');
      resizeMapContainer();
      loadRateData();
      startRateAutoUpdate();
    }

    // 隱藏費率訊息框
    function hideRateMessage() {
      const messageBox = document.getElementById('messageBox');
      messageBox.classList.remove('show');
      document.body.classList.remove('message-shown');
      resizeMapContainer();
      stopRateAutoUpdate();
    }

    // 更新費率訊息框內容
    function updateRateMessageContent(apiResponse) {
      const messageBox = document.getElementById('messageBox');
      const messageStatus = document.getElementById('messageStatus');
      
      if (apiResponse.success) {
        messageBox.className = 'message-container show success';
        messageStatus.textContent = `最後更新: ${new Date().toLocaleTimeString()}`;
        
        document.getElementById('rateName').textContent = apiResponse.data.name || 'N/A';
        document.getElementById('pricePerKwh').textContent = `${apiResponse.data.price_per_kwh || 0} ${apiResponse.data.currency || 'TWD'}`;
        document.getElementById('timeFeePerMin').textContent = `${apiResponse.data.time_fee_per_min || 0} ${apiResponse.data.currency || 'TWD'}`;
        document.getElementById('serviceFee').textContent = `${apiResponse.data.service_fee || 0} ${apiResponse.data.currency || 'TWD'}`;
        document.getElementById('currency').textContent = apiResponse.data.currency || 'TWD';
        document.getElementById('effectiveFrom').textContent = formatDateTime(apiResponse.data.effective_from);
        document.getElementById('effectiveTo').textContent = formatDateTime(apiResponse.data.effective_to);
        document.getElementById('responseCode').textContent = apiResponse.code;
      } else {
        messageBox.className = 'message-container show error';
        messageStatus.textContent = `錯誤 - ${new Date().toLocaleTimeString()}`;
        
        document.getElementById('rateName').textContent = '無法載入';
        document.getElementById('pricePerKwh').textContent = '---';
        document.getElementById('timeFeePerMin').textContent = '---';
        document.getElementById('serviceFee').textContent = '---';
        document.getElementById('currency').textContent = '---';
        document.getElementById('effectiveFrom').textContent = '---';
        document.getElementById('effectiveTo').textContent = '---';
        document.getElementById('responseCode').textContent = apiResponse.code || 'ERROR';
      }

      document.getElementById('rate-update').textContent = `費率更新: ${new Date().toLocaleTimeString()}`;
    }

    // 格式化日期時間
    function formatDateTime(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    }

    // 載入費率資料
    // 載入費率資料 - 適配新的後端 API (只需要 pileId)
async function loadRateData() {
  try {
    // ✅ 只獲取 pileId，移除 user_id 和 user_tier_id
    const getPileId = () => {
      const element = document.getElementById('pile-id');
      if (!element) {
        console.warn('元素 #pile-id 不存在，使用預設值 6');
        return 6;
      }
      const value = element.value ? parseInt(element.value) : 6;
      return isNaN(value) ? 6 : value;
    };

    const pileId = getPileId();

    console.log('費率查詢參數:', { pileId });

    // ✅ 使用駝峰式命名 pileId（而非 pile_id）
    const params = new URLSearchParams({
      pileId: pileId
    });

    const response = await fetch(`/user/purchase/tariff?${params.toString()}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`HTTP ${response.status}: ${errorText}`);
    }

    const apiResponse = await response.json();
    console.log('費率API回應:', apiResponse);
    
    // 檢查後端返回的資料格式
    if (apiResponse.success && apiResponse.data) {
      updateRateMessageContent(apiResponse);
    } else {
      throw new Error(apiResponse.message || '費率資料格式錯誤');
    }

  } catch (error) {
    console.error('載入費率資料失敗:', error);
    updateRateMessageContent({
      success: false,
      code: 'ERROR',
      message: error.message
    });
  }
}

    // 開始自動更新費率
    function startRateAutoUpdate() {
      if (rateUpdateInterval) {
        clearInterval(rateUpdateInterval);
      }
      rateUpdateInterval = setInterval(loadRateData, 30000);
    }

    // 停止自動更新費率
    function stopRateAutoUpdate() {
      if (rateUpdateInterval) {
        clearInterval(rateUpdateInterval);
        rateUpdateInterval = null;
      }
    }

    // 初始化地圖
    function initializeMap() {
      map = L.map('map').setView([23.8, 121], 8);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OSM</a> 貢獻者',
        maxZoom: 19
      }).addTo(map);

      markersGroup = L.layerGroup().addTo(map);
      loadMapMarkers();
      getCurrentLocation();
    }

    // ✅ 修正：載入地圖標記
    function loadMapMarkers(userLat = null, userLng = null, searchDistance = null, stationId = null) {
      showLoading(true);
      
      // ✅ 如果沒有提供座標，使用預設座標（台中）
      if (userLat === null || userLng === null) {
        userLat = DEFAULT_LAT;
        userLng = DEFAULT_LNG;
        console.log('使用預設座標（台中）:', userLat, userLng);
      }
      
      if (searchDistance === null) {
        const distanceInput = document.getElementById('search-distance');
        searchDistance = distanceInput ? distanceInput.value || 10 : 10;
      }
      
      if (stationId === null) {
        const stationIdInput = document.getElementById('station-id');
        const inputValue = stationIdInput ? stationIdInput.value : '';
        stationId = inputValue ? parseInt(inputValue) : null;
      }
      
      // ✅ 確保始終添加 lat 和 lng 參數
      const params = new URLSearchParams();
      params.append('lat', parseFloat(userLat).toFixed(6));
      params.append('lng', parseFloat(userLng).toFixed(6));
      params.append('distance', parseFloat(searchDistance).toString());
      
      if (stationId !== null) {
        params.append('stationId', parseInt(stationId).toString());
      }
      
      const apiUrl = `/index?${params.toString()}`;
      const fallbackUrl = `/map/markers?${params.toString()}`;
      
      clearMarkers();
      
      attemptFetch(apiUrl)
        .catch(error => {
          console.warn('主要API端點失敗，嘗試回退端點:', error.message);
          return attemptFetch(fallbackUrl);
        })
        .then(apiResponse => {
          showLoading(false);
          
          if (!apiResponse.success) {
            throw new Error(apiResponse.message || '載入地圖標記失敗');
          }
          
          const data = apiResponse.data;
          
          if (Array.isArray(data) && data.length > 0) {
            data.forEach(marker => {
              const mapMarker = L.marker([marker.lat, marker.lng], {
                icon: L.icon({
                  iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                  iconSize: [25, 41],
                  iconAnchor: [12, 41],
                  popupAnchor: [1, -34],
                  shadowSize: [41, 41]
                })
              });
              
              const popupContent = `
                <div class="marker-popup">
                  <h4>充電站: ${marker.location_address || '未知位置'}</h4>
                  <p><strong>型號:</strong> ${marker.model || 'N/A'}</p>
                  <p><strong>連接器類型:</strong> ${marker.connector_type || 'N/A'}</p>
                  <p><strong>最大功率:</strong> ${marker.max_kw || 'N/A'} kW</p>
                  <p><strong>韌體版本:</strong> ${marker.firmware_version || 'N/A'}</p>
                  <p><strong>距離:</strong> ${marker.distance || 'N/A'} km</p>
                </div>
              `;
              
              mapMarker.bindPopup(popupContent);
              markersGroup.addLayer(mapMarker);
            });
            
            console.log(`成功載入 ${data.length} 個地圖標記`);
            updateStatus(data.length, {lat: userLat, lng: userLng});
            
            if (data.length > 0) {
              const group = new L.featureGroup(markersGroup.getLayers());
              map.fitBounds(group.getBounds().pad(0.1));
            }
          } else {
            console.warn('沒有找到充電站資料');
            updateStatus(0);
            showError('附近沒有充電站或搜尋範圍內無資料');
          }
        })
        .catch(error => {
          showLoading(false);
          console.error('載入地圖標記失敗:', error);
          showError('載入地圖標記時發生錯誤: ' + error.message);
          updateStatus(0);
        });
    }

    // 輔助函數：嘗試fetch請求
    function attemptFetch(url) {
      return fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
      });
    }

    // ✅ 修正：載入附近充電站
    function loadNearbyStations() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            
            if (userLocationMarker) {
              map.removeLayer(userLocationMarker);
            }
            
            userLocationMarker = L.marker([userLat, userLng], {
              icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
              })
            })
            .addTo(map)
            .bindPopup('您的位置')
            .openPopup();
            
            loadMapMarkers(userLat, userLng);
          },
          function(error) {
            console.error('無法取得位置:', error);
            showError('無法取得您的位置，將使用預設位置（台中）');
            // ✅ 使用預設座標
            loadMapMarkers(DEFAULT_LAT, DEFAULT_LNG);
          }
        );
      } else {
        console.error('瀏覽器不支援地理位置');
        showError('瀏覽器不支援地理位置，將使用預設位置（台中）');
        // ✅ 使用預設座標
        loadMapMarkers(DEFAULT_LAT, DEFAULT_LNG);
      }
    }

    // ✅ 修正：載入所有充電站
    function loadAllStations() {
      loadMapMarkers(DEFAULT_LAT, DEFAULT_LNG);
    }

    // 獲取當前位置
    function getCurrentLocation() {
      if (!navigator.geolocation) {
        console.warn('您的瀏覽器不支援地理定位功能');
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;

          map.setView([lat, lng], 16);

          if (userLocationMarker) {
            map.removeLayer(userLocationMarker);
          }

          L.circle([lat, lng], {
            radius: 30,
            color: '#3f9bff',
            fillColor: '#3f9bff',
            fillOpacity: 0.2,
            weight: 1
          }).addTo(map);

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
          console.warn(errorMessage);
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

    // 顯示更新資料 Modal
    async function showUpdateProfile() {
      try {
        document.getElementById('updateProfileModal').style.display = 'block';
        document.getElementById('updateProfileAlert').innerHTML = '';
        
        // 載入現有用戶資料
        const response = await fetch('/user/info', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        });

        if (response.ok) {
          const data = await response.json();
          if (data.success && data.data) {
            const user = data.data;
            document.getElementById('updateName').value = user.name || '';
            document.getElementById('updateEmail').value = user.email || '';
            document.getElementById('updatePhone').value = user.phone || '';
          }
        }
      } catch (error) {
        console.error('Error loading user profile:', error);
        document.getElementById('updateProfileAlert').innerHTML = '<div class="alert alert-error">載入用戶資料時發生錯誤</div>';
      }
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
        
        alertDiv.innerHTML = '';
        
        if (newPassword.length < 6) {
          alertDiv.innerHTML = '<div class="alert alert-error">新密碼至少需要6個字元！</div>';
          return;
        }

        try {
          const submitBtn = document.querySelector('#passwordForm .btn-submit');
          submitBtn.disabled = true;
          submitBtn.textContent = '更新中...';

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

    // 處理更新資料表單
    function handleUpdateProfileForm() {
      document.getElementById('updateProfileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const alertDiv = document.getElementById('updateProfileAlert');
        
        // 清除之前的提示
        alertDiv.innerHTML = '';
        
        // 驗證必填欄位
        if (!data.name || !data.email) {
          alertDiv.innerHTML = '<div class="alert alert-error">請填寫所有必填欄位！</div>';
          return;
        }
        
        // 驗證Email格式
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
          alertDiv.innerHTML = '<div class="alert alert-error">請輸入有效的Email格式！</div>';
          return;
        }

        try {
          const submitBtn = document.querySelector('#updateProfileForm .btn-submit');
          submitBtn.disabled = true;
          submitBtn.textContent = '更新中...';

          // 檢查是否有 CSRF token
          if (!csrfToken) {
            alertDiv.innerHTML = '<div class="alert alert-error">安全驗證失敗，請重新整理頁面</div>';
            return;
          }

          const response = await fetch('/user/update_profile', {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              name: data.name?.trim(),
              email: data.email?.trim(),
              phone: data.phone?.trim() || undefined
            })
          });

          let responseData = {};
          try {
            responseData = await response.json();
          } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            responseData = { message: '伺服器回應格式錯誤' };
          }

          if (response.ok && responseData.success) {
            alertDiv.innerHTML = '<div class="alert alert-success">會員資料更新成功！</div>';
            setTimeout(() => {
              closeModal('updateProfileModal');
            }, 2000);
          } else {
            let errorMessage = '更新會員資料失敗';
            
            if (response.status === 401) {
              errorMessage = '身份驗證失敗，請重新登入';
            } else if (response.status === 422) {
              errorMessage = '資料格式不正確';
            } else if (responseData.message) {
              errorMessage = responseData.message;
            }
            
            alertDiv.innerHTML = `<div class="alert alert-error">${errorMessage}</div>`;
          }
        } catch (error) {
          console.error('Error updating profile:', error);
          alertDiv.innerHTML = '<div class="alert alert-error">網路連線錯誤，請檢查網路狀態</div>';
        } finally {
          const submitBtn = document.querySelector('#updateProfileForm .btn-submit');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = '更新資料';
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
        
        alertDiv.innerHTML = '';
        
        if (!data.name || !data.email || !data.password || !data.password_confirmation) {
          alertDiv.innerHTML = '<div class="alert alert-error">請填寫所有必填欄位！</div>';
          return;
        }
        
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
            }, 2000);
          } else {
            let errorMessage = '註冊失敗';
            
            if (response.status === 422 && responseData.errors) {
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

          window.location.href = '/login';
        } catch (error) {
          console.error('Logout error:', error);
          window.location.href = '/login';
        }
      }
    }

    // 點擊 Modal 外部關閉
    window.onclick = function(event) {
      const userModal = document.getElementById('userInfoModal');
      const passwordModal = document.getElementById('changePasswordModal');
      const updateProfileModal = document.getElementById('updateProfileModal');
      const registerModal = document.getElementById('registerModal');
      
      if (event.target == userModal) {
        userModal.style.display = 'none';
      }
      if (event.target == passwordModal) {
        passwordModal.style.display = 'none';
      }
      if (event.target == updateProfileModal) {
        updateProfileModal.style.display = 'none';
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
      handleUpdateProfileForm();
      handleRegisterForm();
    });

    // 視窗大小改變時重新調整地圖容器高度
    window.addEventListener('resize', resizeMapContainer);
  </script>
</body>
</html>