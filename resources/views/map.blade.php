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
    
    /* Success Modal Styles */
    .success-modal-backdrop {
      backdrop-filter: blur(2px);
      transition: opacity 0.3s ease;
    }
    
    .success-modal {
      animation: successModalSlideIn 0.3s ease-out;
      transition: all 0.3s ease;
    }
    
    @keyframes successModalSlideIn {
      from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.8);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }
    
    #success-close:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
    }
    
    #success-close:active {
      transform: translateY(0);
    }
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
      <button onclick="openMyReservations()" class="control-btn btn-register">
        查看我的預約
      </button>
      <!-- </button>
      <button onclick="showRegister()" class="control-btn btn-register">
        註冊新用戶
      </button> -->
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
    
    <div class="form-group">
      <label for="pile-id">充電樁ID</label>
      <input type="number" id="pile-id" placeholder="用於費率查詢" value="6">
    </div>
    
    <div class="button-group">
      <button onclick="loadNearbyStations()" class="station-btn">載入附近充電站</button>
      <button onclick="loadAllStations()" class="station-btn secondary">載入所有充電站</button>
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

  <!-- Reservation Modal -->
  <div id="reservation-backdrop" class="reservation-modal-backdrop" style="position: fixed; inset: 0; background: rgba(0,0,0,0.35); display: none; z-index: 1002;"></div>
  <div id="reservation-modal" class="reservation-modal" role="dialog" aria-modal="true" style="position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.15); width: 500px; max-width: calc(100% - 32px); display: none; z-index: 1003; overflow: hidden;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #2b7a0b 0%, #1e5a08 100%); color: white; padding: 20px; text-align: center;">
      <h2 style="margin: 0; font-size: 20px; font-weight: 600;">我要預約</h2>
    </div>
    
    <!-- Body -->
    <div style="padding: 28px;">
      <!-- Station Info -->
      <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
            <div style="font-size: 12px; color: #6c757d; margin-bottom: 6px; font-weight: 500;">站點</div>
            <div id="resv-address" style="font-weight: 600; color: #2d3748; font-size: 14px;">-</div>
          </div>
          <div>
            <div style="font-size: 12px; color: #6c757d; margin-bottom: 6px; font-weight: 500;">型號</div>
            <div id="resv-model" style="font-weight: 600; color: #2d3748; font-size: 14px;">-</div>
          </div>
          <div>
            <div style="font-size: 12px; color: #6c757d; margin-bottom: 6px; font-weight: 500;">接頭</div>
            <div id="resv-connector" style="font-weight: 600; color: #2d3748; font-size: 14px;">-</div>
          </div>
          <div>
            <div style="font-size: 12px; color: #6c757d; margin-bottom: 6px; font-weight: 500;">最大功率</div>
            <div id="resv-maxkw" style="font-weight: 600; color: #2d3748; font-size: 14px;">-</div>
          </div>
        </div>
      </div>
      
      <input type="hidden" id="resv-pile-id" />
      
      <!-- Time Selection -->
      <div style="margin-bottom: 24px;">
        <!-- Start Time -->
        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 15px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">開始時間</label>
          <div class="custom-datetime-picker" style="display: flex; gap: 10px; align-items: center; background: #fff; padding: 14px; border-radius: 12px; border: 2px solid #e2e8f0; transition: all 0.2s;">
            <input type="date" id="resv-start-date" style="padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 8px; flex: 1; background: white; font-size: 15px; transition: border-color 0.2s;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <select id="resv-start-hour" style="padding: 12px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 15px; min-width: 75px; transition: border-color 0.2s;">
                <!-- Options will be populated by JavaScript -->
              </select>
              <span style="font-weight: bold; color: #4a5568; font-size: 20px;">:</span>
              <select id="resv-start-minute" style="padding: 12px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 15px; min-width: 75px; transition: border-color 0.2s;">
                <option value="00">00</option><option value="15">15</option><option value="30">30</option><option value="45">45</option>
              </select>
            </div>
          </div>
        </div>
        
        <!-- End Time -->
        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 15px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">結束時間</label>
          <div class="custom-datetime-picker" style="display: flex; gap: 10px; align-items: center; background: #fff; padding: 14px; border-radius: 12px; border: 2px solid #e2e8f0; transition: all 0.2s;">
            <input type="date" id="resv-end-date" style="padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 8px; flex: 1; background: white; font-size: 15px; transition: border-color 0.2s;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <select id="resv-end-hour" style="padding: 12px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 15px; min-width: 75px; transition: border-color 0.2s;">
                <!-- Options will be populated by JavaScript -->
              </select>
              <span style="font-weight: bold; color: #4a5568; font-size: 20px;">:</span>
              <select id="resv-end-minute" style="padding: 12px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: white; font-size: 15px; min-width: 75px; transition: border-color 0.2s;">
                <option value="00">00</option><option value="15">15</option><option value="30">30</option><option value="45">45</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <!-- Error Message -->
      <div id="resv-error" style="color: #e53e3e; font-size: 13px; min-height: 20px; margin-bottom: 20px; padding: 8px 12px; background: #fed7d7; border-radius: 8px; display: none;"></div>
    </div>
    
    <!-- Actions -->
    <div style="display: flex; gap: 16px; padding: 24px 28px; background: #f8f9fa; border-top: 1px solid #e2e8f0;">
      <button id="resv-cancel" style="flex: 1; padding: 14px 24px; border-radius: 12px; border: 2px solid #e2e8f0; background: white; color: #4a5568; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s;">取消</button>
      <button id="resv-submit" style="flex: 1; padding: 14px 24px; border-radius: 12px; border: none; background: linear-gradient(135deg, #2b7a0b 0%, #1e5a08 100%); color: white; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s;">我要預約</button>
    </div>
    
    <style>
      .custom-datetime-picker input:focus,
      .custom-datetime-picker select:focus {
        outline: none;
        border-color: #2b7a0b !important;
        box-shadow: 0 0 0 3px rgba(43, 122, 11, 0.1);
      }
      .custom-datetime-picker:hover {
        border-color: #cbd5e0;
      }
      #resv-cancel:hover {
        background: #f7fafc;
        border-color: #cbd5e0;
      }
      #resv-submit:hover {
        background: linear-gradient(135deg, #1e5a08 0%, #164a06 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(43, 122, 11, 0.3);
      }
    </style>
  </div>

  <!-- Success Modal -->
  <div id="success-backdrop" class="success-modal-backdrop" style="position: fixed; inset: 0; background: rgba(0,0,0,0.35); display: none; z-index: 1004;"></div>
  <div id="success-modal" class="success-modal" role="dialog" aria-modal="true" style="position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); width: 380px; max-width: calc(100% - 32px); display: none; z-index: 1005;">
    <div style="padding: 24px; text-align: center;">
      <div style="width: 60px; height: 60px; margin: 0 auto 16px; background: linear-gradient(135deg, #00b894 0%, #00a085 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h3 id="success-title" style="margin: 0 0 8px; font-size: 20px; font-weight: 600; color: #2d3748;">預約成功！</h3>
      <p id="success-message" style="margin: 0 0 20px; color: #718096; font-size: 14px; line-height: 1.5;">您的充電站預約已成功建立</p>
      <button id="success-close" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease;">確定</button>
    </div>
  </div>

  <!-- My Reservations Modal -->
  <div id="myresv-backdrop" class="reservation-modal-backdrop" style="position: fixed; inset: 0; background: rgba(0,0,0,0.35); display: none; z-index: 1002;"></div>
  <div id="myresv-modal" class="reservation-modal" role="dialog" aria-modal="true" style="position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 520px; max-width: calc(100% - 32px); display: none; z-index: 1003;">
    <header style="padding:14px 16px;border-bottom:1px solid #eee;font-weight:bold;display:flex;justify-content:space-between;align-items:center;">我的預約
      <button id="myresv-close" class="btn btn-secondary" style="padding:4px 8px;border:none;border-radius:6px;">關閉</button>
    </header>
    <div class="body" style="padding:16px;">
      <div id="myresv-list" style="display:flex;flex-direction:column;gap:10px;"></div>
      <div id="myresv-error" style="color:#d63031;font-size:12px;min-height:16px;margin-top:8px;"></div>
    </div>
  </div>

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
    // 全域變數
    let map;
    let csrfToken = '';
    let authToken = '';
    let userLocationMarker = null;
    let markersGroup;
    let rateUpdateInterval;

    // 預設座標（台中市中心）
    const DEFAULT_LAT = 24.1477;
    const DEFAULT_LNG = 120.6736;

    // ✅ 修正：加入 Authorization header
    function getAuthHeaders() {
       const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      };
    
      const token = localStorage.getItem('auth_token');
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
    console.log('✅ Authorization header 已加入，token 前20字:', token.substring(0, 20));
  } else {
    console.warn('⚠️ localStorage 中沒有 token');
  }
  
  return headers;

    }

    // 初始化 Token
function initializeAuthToken() {
  // 從 localStorage 讀取 token
  const token = localStorage.getItem('auth_token');
  
  if (!token) {
    console.warn('未找到認證 token，某些功能可能無法使用');
  } else {
    console.log('成功載入認證 token，長度:', token.length);
  }
}


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

    // 顯示成功彈窗
    function showSuccess(message) {
      const successTitleEl = document.getElementById('success-title');
      const successMessageEl = document.getElementById('success-message');
      
      if (successTitleEl) {
        successTitleEl.textContent = message || '操作成功！';
      }
      if (successMessageEl) {
        successMessageEl.textContent = message || '操作成功！';
      }
      
      // 顯示成功彈窗
      document.getElementById('success-backdrop').style.display = 'block';
      document.getElementById('success-modal').style.display = 'block';
      
      // 自動關閉彈窗（可選）
      setTimeout(() => {
        hideSuccessModal();
      }, 3000);
    }

    // 隱藏成功彈窗
    function hideSuccessModal() {
      document.getElementById('success-backdrop').style.display = 'none';
      document.getElementById('success-modal').style.display = 'none';
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

async function loadRateData() {
  try {
    // ✅ 步驟1：讀取 token
    const token = localStorage.getItem('auth_token');
    
    console.log('===== TOKEN 檢查 =====');
    console.log('token 值:', token);
    console.log('token 長度:', token ? token.length : 0);
    console.log('token 前20字:', token ? token.substring(0, 20) : 'N/A');
    
    if (!token) {
      throw new Error('未找到認證 token，請先登入');
    }

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
    const params = new URLSearchParams({ pile_id: pileId });

    // ✅ 步驟2：組裝 headers - 直接使用剛讀取的 token
 const bearerToken = 'Bearer ' + token;
    console.log('===== Authorization 組裝 =====');
    console.log('Bearer Token 前30字:', bearerToken.substring(0, 30));
    console.log('Bearer Token 長度:', bearerToken.length);
    
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      'Authorization': bearerToken // ✅ 使用變數
    };
    
    console.log('===== HEADERS 檢查 =====');
    console.log('完整 headers:', headers);
    console.log('headers.Authorization:', headers['Authorization']);
    console.log('headers.Authorization 長度:', headers['Authorization'].length);
    console.log('headers.Authorization 前30字:', headers['Authorization'].substring(0, 30));
    console.log('與 localStorage 是否一致:', headers['Authorization'] === bearerToken);

    // ✅ 步驟3：發送請求
    console.log('===== 發送請求 =====');
    console.log('URL:', `/user/purchase/tariff?${params.toString()}`);
    
    const response = await fetch(`/user/purchase/tariff?${params.toString()}`, {
      method: 'GET',
      headers: headers,
      credentials: 'same-origin'
    });

    console.log('===== 回應檢查 =====');
    console.log('Status:', response.status);
    console.log('OK:', response.ok);

    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('auth_token');
        throw new Error('認證已過期，請重新登入');
      }
      const errorText = await response.text();
      throw new Error(`HTTP ${response.status}: ${errorText}`);
    }

    const apiResponse = await response.json();
    console.log('費率API回應:', apiResponse);
    
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

    // 載入地圖標記
    function loadMapMarkers(userLat = null, userLng = null, searchDistance = null, stationId = null) {
      showLoading(true);
      
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
                  <div style="margin-top:8px;">
                    <button
                      class="reserve-btn"
                      data-pile-id="${marker.id}"
                      data-address="${marker.location_address || ''}"
                      data-model="${marker.model || ''}"
                      data-connector="${marker.connector_type || ''}"
                      data-maxkw="${marker.max_kw || ''}"
                      data-firmware="${marker.firmware_version || ''}"
                      style="background:#2b7a0b;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;"
                    >我要預約</button>
                  </div>
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
        },
        credentials: 'same-origin'
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
      });
    }

    // 載入附近充電站
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
            loadMapMarkers(DEFAULT_LAT, DEFAULT_LNG);
          }
        );
      } else {
        console.error('瀏覽器不支援地理位置');
        showError('瀏覽器不支援地理位置，將使用預設位置（台中）');
        loadMapMarkers(DEFAULT_LAT, DEFAULT_LNG);
      }
    }

    // 載入所有充電站
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
          headers: getAuthHeaders(),
          credentials: 'same-origin'
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
        
        const response = await fetch('/user/info', {
          method: 'GET',
          headers: getAuthHeaders(),
          credentials: 'same-origin'
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
          alertDiv.innerHTML = '<div class="alert alert-error">新密碼至少需要6個字元!</div>';
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
              ...getAuthHeaders()
            },
            credentials: 'same-origin',
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
            alertDiv.innerHTML = '<div class="alert alert-success">密碼更新成功!</div>';
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
        
        alertDiv.innerHTML = '';
        
        if (!data.name || !data.email) {
          alertDiv.innerHTML = '<div class="alert alert-error">請填寫所有必填欄位!</div>';
          return;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
          alertDiv.innerHTML = '<div class="alert alert-error">請輸入有效的Email格式!</div>';
          return;
        }

        try {
          const submitBtn = document.querySelector('#updateProfileForm .btn-submit');
          submitBtn.disabled = true;
          submitBtn.textContent = '更新中...';

          if (!csrfToken) {
            alertDiv.innerHTML = '<div class="alert alert-error">安全驗證失敗，請重新整理頁面</div>';
            return;
          }

          const response = await fetch('/user/update_profile', {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              ...getAuthHeaders()
            },
            credentials: 'same-origin',
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
            alertDiv.innerHTML = '<div class="alert alert-success">會員資料更新成功!</div>';
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
          alertDiv.innerHTML = '<div class="alert alert-error">請填寫所有必填欄位!</div>';
          return;
        }
        
        if (data.password !== data.password_confirmation) {
          alertDiv.innerHTML = '<div class="alert alert-error">密碼與確認密碼不符!</div>';
          return;
        }
        
        if (data.password.length < 6) {
          alertDiv.innerHTML = '<div class="alert alert-error">密碼至少需要6個字元!</div>';
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
            credentials: 'same-origin',
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
            alertDiv.innerHTML = '<div class="alert alert-success">註冊成功!即將跳轉...</div>';
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

    // ✅ 修正：登出功能 - 移除 Authorization header
    async function logout() {
      if (confirm('確定要登出嗎?')) {
        try {
          const response = await fetch('/logout', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
          });

          // 可選：清除 localStorage（如果有儲存其他資料）
          localStorage.clear();
          window.location.href = '/login';
        } catch (error) {
          console.error('Logout error:', error);
          localStorage.clear();
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
       // ✅ 添加：檢查 token 有效性
  const token = localStorage.getItem('auth_token');
  if (token) {
    try {
      const parts = token.split('.');
      const payload = JSON.parse(atob(parts[1]));
      const isExpired = Date.now() > payload.exp * 1000;
      
      if (isExpired) {
        console.error('❌ Token 已過期，清除並導向登入頁');
        localStorage.clear();
        alert('登入已過期，請重新登入');
        window.location.href = '/login';
        return;
      }
      
      console.log('✅ Token 有效');
      authToken = token;
    } catch (error) {
      console.error('❌ Token 格式錯誤:', error);
      localStorage.clear();
      window.location.href = '/login';
      return;
    }
  } else {
    console.warn('⚠️ 未找到 token');
  }
      initializeAuthToken(); 
      resizeMapContainer();
      initializeMap();
      handlePasswordForm();
      handleUpdateProfileForm();
      handleRegisterForm();
    });

    // 登入成功後儲存 token
function saveAuthToken(token) {
  authToken = token;
  localStorage.setItem('auth_token', token);
  console.log('Token 已儲存');
}


    // 視窗大小改變時重新調整地圖容器高度
    window.addEventListener('resize', resizeMapContainer);

    // ========== Reservation modal logic ==========
    // Helpers for concurrency and parsing
    let requestLock = false;
    async function withLock(fn) {
      if (requestLock) { return; }
      requestLock = true;
      try { await fn(); } finally { requestLock = false; }
    }

    async function safeJsonResponse(resp) {
      const ct = resp.headers.get('content-type') || '';
      if (resp.status === 204 || !ct.includes('application/json')) {
        try { return JSON.parse(await resp.text()); } catch (_) { return null; }
      }
      try { return await resp.json(); } catch (_) { return null; }
    }

    function uuidv4() {
      if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
      return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    }
    const sleep = (ms) => new Promise(r => setTimeout(r, ms));

    // Smart DateTime Picker with validation
    function initializeSmartDateTimePicker() {
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const maxDate = new Date(today.getTime() + 14 * 24 * 60 * 60 * 1000); // 14 days from today
      
      // Set date constraints
      document.getElementById('resv-start-date').min = formatDate(today);
      document.getElementById('resv-start-date').max = formatDate(maxDate);
      document.getElementById('resv-end-date').min = formatDate(today);
      document.getElementById('resv-end-date').max = formatDate(maxDate);
      
      // Populate hour options based on current time and rules
      populateHourOptions('resv-start-hour', 'resv-start-date');
      populateHourOptions('resv-end-hour', 'resv-end-date');
      
      // Add event listeners for smart updates
      document.getElementById('resv-start-date').addEventListener('change', () => {
        populateHourOptions('resv-start-hour', 'resv-start-date');
        updateEndTimeOptions();
      });
      
      document.getElementById('resv-end-date').addEventListener('change', () => {
        populateHourOptions('resv-end-hour', 'resv-end-date');
        updateEndTimeOptions();
      });
      
      document.getElementById('resv-start-hour').addEventListener('change', updateEndTimeOptions);
      document.getElementById('resv-start-minute').addEventListener('change', updateEndTimeOptions);
    }
    
    function populateHourOptions(hourSelectId, dateSelectId) {
      const hourSelect = document.getElementById(hourSelectId);
      const dateSelect = document.getElementById(dateSelectId);
      const selectedDate = new Date(dateSelect.value);
      const now = new Date();
      
      // Clear existing options
      hourSelect.innerHTML = '';
      
      // Determine if this is today
      const isToday = selectedDate.toDateString() === now.toDateString();
      const minAdvanceTime = new Date(now.getTime() + 15 * 60000); // 15 minutes from now
      
      // Add hour options
      for (let hour = 0; hour < 24; hour++) {
        const option = document.createElement('option');
        option.value = String(hour).padStart(2, '0');
        option.textContent = String(hour).padStart(2, '0');
        
        // Disable hours that are too early
        if (isToday && hour < minAdvanceTime.getHours()) {
          option.disabled = true;
        }
        
        hourSelect.appendChild(option);
      }
    }
    
    function updateEndTimeOptions() {
      const startDate = document.getElementById('resv-start-date').value;
      const startHour = parseInt(document.getElementById('resv-start-hour').value);
      const startMinute = parseInt(document.getElementById('resv-start-minute').value);
      
      if (!startDate || isNaN(startHour) || isNaN(startMinute)) return;
      
      const startDateTime = new Date(`${startDate}T${String(startHour).padStart(2, '0')}:${String(startMinute).padStart(2, '0')}`);
      const minEndTime = new Date(startDateTime.getTime() + 30 * 60000); // 30 minutes later
      const maxEndTime = new Date(startDateTime.getTime() + 4 * 60 * 60000); // 4 hours later
      
      // Update end date if needed
      const endDate = document.getElementById('resv-end-date');
      const endHour = parseInt(document.getElementById('resv-end-hour').value);
      const endMinute = parseInt(document.getElementById('resv-end-minute').value);
      
      if (endHour !== null && !isNaN(endHour) && endMinute !== null && !isNaN(endMinute)) {
        const endDateTime = new Date(`${endDate.value}T${String(endHour).padStart(2, '0')}:${String(endMinute).padStart(2, '0')}`);
        
        if (endDateTime <= startDateTime) {
          // Auto-adjust end time to minimum duration
          const adjustedEnd = new Date(startDateTime.getTime() + 30 * 60000);
          endDate.value = formatDate(adjustedEnd);
          document.getElementById('resv-end-hour').value = String(adjustedEnd.getHours()).padStart(2, '0');
          document.getElementById('resv-end-minute').value = String(adjustedEnd.getMinutes()).padStart(2, '0');
          
          console.log('Auto-adjusted end time to:', {
            date: formatDate(adjustedEnd),
            time: `${String(adjustedEnd.getHours()).padStart(2, '0')}:${String(adjustedEnd.getMinutes()).padStart(2, '0')}`,
            duration: '30 minutes'
          });
        }
      }
    }
    
    function formatDate(d) {
      const pad = (n) => String(n).padStart(2, '0');
      return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    }
    
    // Format display time from UTC to local timezone
    function formatDisplayTime(timeString) {
      if (!timeString) return '-';
      
      try {
        // Backend returns local time in YYYY-MM-DD HH:mm:ss format
        // Display as-is without timezone conversion
        console.log('Time formatting:', {
          input: timeString,
          display: timeString,
          note: 'Backend returns local time, displaying as-is'
        });
        
        return timeString;
      } catch (e) {
        console.error('Error formatting time:', timeString, e);
        return timeString.replace('T', ' ');
      }
    }

    // (Toast helpers removed per request)
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.reserve-btn');
      if (btn) {
        const pileId = btn.getAttribute('data-pile-id');
        document.getElementById('resv-pile-id').value = pileId;
        document.getElementById('resv-address').textContent = btn.getAttribute('data-address') || '-';
        document.getElementById('resv-model').textContent = btn.getAttribute('data-model') || '-';
        document.getElementById('resv-connector').textContent = btn.getAttribute('data-connector') || '-';
        document.getElementById('resv-maxkw').textContent = btn.getAttribute('data-maxkw') ? (btn.getAttribute('data-maxkw') + ' kW') : '-';

        // Default start/end: next aligned 15-min slot for 1 hour
        const step = 15; // minutes
        const nowDt = new Date();
        const addMinutes = (d, m) => new Date(d.getTime() + m*60000);
        const ceilToStep = (d) => {
          const aligned = new Date(d);
          aligned.setSeconds(0,0);
          const minutes = aligned.getMinutes();
          const remainder = minutes % step;
          if (remainder !== 0) aligned.setMinutes(minutes + (step - remainder));
          return aligned;
        };
        const start = ceilToStep(addMinutes(nowDt, 15));
        const end = addMinutes(start, 60);
        
        // Set custom datetime picker values
        const pad = (n) => String(n).padStart(2,'0');
        const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        const formatTime = (d) => ({
          hour: pad(d.getHours()),
          minute: pad(d.getMinutes())
        });
        
        // Initialize smart datetime picker first
        initializeSmartDateTimePicker();
        
        // Set start time
        document.getElementById('resv-start-date').value = formatDate(start);
        const startTime = formatTime(start);
        document.getElementById('resv-start-hour').value = startTime.hour;
        document.getElementById('resv-start-minute').value = startTime.minute;
        
        // Set end time
        document.getElementById('resv-end-date').value = formatDate(end);
        const endTime = formatTime(end);
        document.getElementById('resv-end-hour').value = endTime.hour;
        document.getElementById('resv-end-minute').value = endTime.minute;
        
        console.log('Default times set:', {
          start: `${formatDate(start)} ${startTime.hour}:${startTime.minute}`,
          end: `${formatDate(end)} ${endTime.hour}:${endTime.minute}`,
          duration: Math.round((end - start) / 60000) + ' minutes'
        });
        
        document.getElementById('resv-error').textContent = '';

        document.getElementById('reservation-backdrop').style.display = 'block';
        document.getElementById('reservation-modal').style.display = 'block';
      }
    });

    document.getElementById('resv-cancel').addEventListener('click', () => {
      document.getElementById('reservation-backdrop').style.display = 'none';
      document.getElementById('reservation-modal').style.display = 'none';
    });
    document.getElementById('reservation-backdrop').addEventListener('click', () => {
      document.getElementById('reservation-backdrop').style.display = 'none';
      document.getElementById('reservation-modal').style.display = 'none';
    });

    // Success Modal Event Listeners
    document.getElementById('success-close').addEventListener('click', () => {
      hideSuccessModal();
    });
    document.getElementById('success-backdrop').addEventListener('click', () => {
      hideSuccessModal();
    });

    document.getElementById('resv-submit').addEventListener('click', async () => withLock(async () => {
      console.log('Submit button clicked');
      
      const pileId = parseInt(document.getElementById('resv-pile-id').value || '0');
      
      // Get values from custom datetime picker
      const startDate = document.getElementById('resv-start-date').value;
      const startHour = document.getElementById('resv-start-hour').value;
      const startMinute = document.getElementById('resv-start-minute').value;
      const endDate = document.getElementById('resv-end-date').value;
      const endHour = document.getElementById('resv-end-hour').value;
      const endMinute = document.getElementById('resv-end-minute').value;
      
      console.log('Form values:', { pileId, startDate, startHour, startMinute, endDate, endHour, endMinute });
      
      const startStr = `${startDate}T${startHour}:${startMinute}`;
      const endStr = `${endDate}T${endHour}:${endMinute}`;
      const errEl = document.getElementById('resv-error');
      errEl.textContent = '';
      errEl.style.display = 'none';

      if (!pileId || !startStr || !endStr) {
        errEl.textContent = '請完整填寫';
        errEl.style.display = 'block';
        console.log('Form validation failed:', { pileId, startStr, endStr });
        return;
      }

      // Convert local datetime to API format (subtract 8 hours for Taiwan timezone)
      const toApiFormat = (local) => {
        const d = new Date(local);
        // Subtract 8 hours to compensate for Taiwan timezone (UTC+8)
        const adjustedTime = new Date(d.getTime() - 8 * 60 * 60 * 1000);
        
        const pad = (n) => String(n).padStart(2, '0');
        const year = adjustedTime.getFullYear();
        const month = pad(adjustedTime.getMonth() + 1);
        const day = pad(adjustedTime.getDate());
        const hours = pad(adjustedTime.getHours());
        const minutes = pad(adjustedTime.getMinutes());
        const seconds = pad(adjustedTime.getSeconds());
        
        // Return in YYYY-MM-DDTHH:mm:ss format
        return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
      };
      
      console.log('Time conversion debug:', {
        userInput: {
          startDate: startDate,
          startHour: startHour,
          startMinute: startMinute,
          endDate: endDate,
          endHour: endHour,
          endMinute: endMinute
        },
        constructedStrings: {
          startStr: startStr,
          endStr: endStr
        },
        parsedDates: {
          startDate: new Date(startStr),
          endDate: new Date(endStr)
        },
        timezoneAdjustment: {
          originalStart: new Date(startStr).toISOString(),
          adjustedStart: new Date(new Date(startStr).getTime() - 8 * 60 * 60 * 1000).toISOString(),
          originalEnd: new Date(endStr).toISOString(),
          adjustedEnd: new Date(new Date(endStr).getTime() - 8 * 60 * 60 * 1000).toISOString(),
          note: "Subtracted 8 hours for Taiwan timezone compensation"
        },
        apiFormat: {
          apiStart: toApiFormat(startStr),
          apiEnd: toApiFormat(endStr)
        },
        timezoneInfo: {
          offset: new Date().getTimezoneOffset(),
          timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
        }
      });

      // Try to get Bearer token from session helper endpoint
      let authHeader = {};
      try {
        const tokResp = await fetch('/auth/token', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (tokResp.ok) {
          const tokJson = await tokResp.json();
          if (tokJson?.success && tokJson?.token) {
            authHeader['Authorization'] = 'Bearer ' + tokJson.token;
          }
        }
      } catch (_) { /* ignore */ }

      // Local pre-checks per minimal rules
      const toDate = (s) => new Date(s);
      const sd = toDate(startStr);
      const ed = toDate(endStr);
      if (!(sd instanceof Date) || isNaN(sd) || !(ed instanceof Date) || isNaN(ed)) {
        errEl.textContent = '傳入的時間格式錯誤，或是日期時間不符合標準';
        return;
      }
      if (ed <= sd) {
        errEl.textContent = '預約結束時間必須晚於開始時間';
        errEl.style.display = 'block';
        console.log('End time validation failed:', { start: startStr, end: endStr });
        return;
      }
      
      // Check minimum advance reservation time (15 minutes)
      const now = new Date();
      const minAdvanceTime = new Date(now.getTime() + 15 * 60000); // 15 minutes from now
      if (sd < minAdvanceTime) {
        errEl.textContent = '預約的開始時間早於現在時間（至少需要提前15分鐘預約）';
        return;
      }
      
      // Check bookable date range (14 days from today)
      const maxBookableDate = new Date(now.getTime() + 14 * 24 * 60 * 60 * 1000); // 14 days from now
      if (sd > maxBookableDate) {
        errEl.textContent = '超出可預約的日期範圍（只能預約今天起14天內的時間）';
        return;
      }
      const minutesBetween = Math.round((ed - sd) / 60000);
      if (minutesBetween < 30 || minutesBetween > 240) {
        errEl.textContent = '預約的時長不符合規則（小於30分鐘或大於4小時）';
        return;
      }
      
      // Check time slot granularity (15-minute intervals)
      const startMinutes = sd.getMinutes();
      const endMinutes = ed.getMinutes();
      if (startMinutes % 15 !== 0 || endMinutes % 15 !== 0) {
        errEl.textContent = '預約的時間沒有對齊時間粒度（只能選00、15、30、45分）';
        return;
      }

      const submitBtn = document.getElementById('resv-submit');
      submitBtn.disabled = true;
      try {
        // Guard: ensure no active reservation (use /reservations/top)
        try {
          const chk = await fetch('/reservations/top', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'include' });
          const chkJson = await safeJsonResponse(chk);
          if (chk.ok && chkJson && chkJson.success && chkJson.data && chkJson.data.status && chkJson.data.status !== 'CANCELLED' && chkJson.data.status !== 'CANCELED') {
            errEl.textContent = '你目前已有生效預約，請先取消或更改時段';
            submitBtn.disabled = false;
            return;
          }
        } catch (_) { /* ignore */ }

        const requestBody = {
          pile_id: pileId,
          start_time: toApiFormat(startStr),
          end_time: toApiFormat(endStr)
        };
        
        console.log('Sending reservation request:', requestBody);
        
        const resp = await fetch('/reservations', {
          method: 'POST',
          headers: Object.assign({
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Idempotency-Key': uuidv4()
          }, authHeader),
          credentials: 'include',
          body: JSON.stringify(requestBody)
        });
        
        console.log('Response status:', resp.status);
        console.log('Response headers:', resp.headers);
        
        let json = await safeJsonResponse(resp) || {};
        console.log('Response JSON:', json);
        
        // Debug response times
        if (json.data) {
          console.log('Backend response times:', {
            backendStart: json.data.start_time,
            backendEnd: json.data.end_time,
            userSelectedStart: startStr,
            userSelectedEnd: endStr,
            timeDifference: {
              startDiff: json.data.start_time ? 
                (new Date(json.data.start_time) - new Date(startStr)) / (1000 * 60 * 60) + ' hours' : 'N/A',
              endDiff: json.data.end_time ? 
                (new Date(json.data.end_time) - new Date(endStr)) / (1000 * 60 * 60) + ' hours' : 'N/A'
            }
          });
        }
        
        if (!resp.ok || json.success === false) {
          let msg = json.message || `預約失敗 (HTTP ${resp.status})`;
          
          // Check API response structure according to documentation
          console.log('API Response structure:', {
            success: json.success,
            code: json.code,
            message: json.message,
            hasData: !!json.data,
            dataKeys: json.data ? Object.keys(json.data) : []
          });
          
          // Map backend codes to UI
          if (json?.data?.error_code === 'INVALID_DATETIME') msg = '傳入的時間格式錯誤，或是日期時間不符合標準';
          if (json?.data?.error_code === 'START_IN_PAST') msg = '預約的開始時間早於現在時間';
          if (json?.data?.error_code === 'END_BEFORE_START') msg = '預約結束時間比開始時間還早';
          if (json?.data?.error_code === 'OUT_OF_BOOKABLE_RANGE') msg = '超出可預約的日期範圍（預設是今天起14天內）';
          if (json?.data?.error_code === 'NOT_MATCH_SLOT_GRANULARITY') msg = '預約的時間沒有對齊時間粒度（只能選00、15、30、45分）';
          if (json?.data?.error_code === 'DURATION_OUT_OF_RANGE') msg = '預約的時長不符合規則（小於30分鐘或大於4小時）';
          if (json?.data?.error_code === 'CROSS_DAY_NOT_ALLOWED') msg = '嘗試跨日預約，但系統設定不允許';
          if (json?.data?.error_code === 'OVERLAPPED_WITH_OTHERS') msg = '預約時間和其他已存在的預約衝突';
          if (resp.status === 401) msg = '請先登入再預約';
          if (json?.data?.error_code === 'USER_ACTIVE') msg = '你已有一筆尚未結束的預約';
          if (resp.status === 409 && !json?.data?.error_code) msg = '該時段不可用或與其他預約衝突';
          if (resp.status === 400) msg = '請求格式錯誤，請檢查輸入資料';
          
          errEl.textContent = msg;
          errEl.style.display = 'block';
          console.log('Error message:', msg);
          return;
        }
        // success
        console.log('Reservation successful!');
        // 提示：預約成功
        if (typeof showSuccess === 'function') {
          showSuccess('預約成功');
        } else {
          console.log('showSuccess function not found');
        }
        document.getElementById('reservation-backdrop').style.display = 'none';
        document.getElementById('reservation-modal').style.display = 'none';
      } catch (e) {
        console.error('Reservation error:', e);
        errEl.textContent = '連線失敗，請稍後再試';
        errEl.style.display = 'block';
      } finally {
        submitBtn.disabled = false;
      }
    }));
    // ========== end Reservation modal logic ==========

    // ========== My Reservations (view & cancel) ==========
    let myResvPollTimer = null;
    function stopMyResvPolling() {
      if (myResvPollTimer) {
        clearInterval(myResvPollTimer);
        myResvPollTimer = null;
      }
    }

    let lastMyResvKey = null;
    function keyOfResv(d) {
      const addr = (d.location_address || (d.pile_response && d.pile_response.location_address) || '');
      const lat = (typeof d.lat === 'number') ? d.lat : (d.pile_response && typeof d.pile_response.lat === 'number' ? d.pile_response.lat : '');
      const lng = (typeof d.lng === 'number') ? d.lng : (d.pile_response && typeof d.pile_response.lng === 'number' ? d.pile_response.lng : '');
      return [d.id, d.start_time, d.end_time, d.status, addr, lat, lng].join('|');
    }

    function renderMyReservation(data, listEl) {
      listEl.innerHTML = '';
      const item = document.createElement('div');
      item.style.border = '1px solid #eee';
      item.style.borderRadius = '8px';
      item.style.padding = '10px';
      const addr = (data.location_address || (data.pile_response && data.pile_response.location_address) || '');
      const lat = (typeof data.lat === 'number') ? data.lat : (data.pile_response && typeof data.pile_response.lat === 'number' ? data.pile_response.lat : null);
      const lng = (typeof data.lng === 'number') ? data.lng : (data.pile_response && typeof data.pile_response.lng === 'number' ? data.pile_response.lng : null);
      const gmap = (lat !== null && lng !== null)
        ? `https://www.google.com/maps?q=${lat},${lng}`
        : (addr ? `https://www.google.com/maps?q=${encodeURIComponent(addr)}` : '');

      item.innerHTML = `
        <div>開始：${formatDisplayTime(data.start_time)}</div>
        <div>結束：${formatDisplayTime(data.end_time)}</div>
        <div>地點：${addr || '-'}
          ${gmap ? `<a href="${gmap}" target="_blank" rel="noopener" title="在 Google Maps 開啟" style="margin-left:6px; display:inline-flex; align-items:center;">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="#2563eb" aria-hidden="true">
              <path d="M12 2C8.686 2 6 4.686 6 8c0 5.25 6 12 6 12s6-6.75 6-12c0-3.314-2.686-6-6-6zm0 8.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
            </svg>
          </a>` : ''}
        </div>
        <div>狀態：<span id="myresv-status">${data.status || ''}</span></div>
        <div style="margin-top:8px;display:flex;gap:8px;">
          <button id="btnCancelResv" class="btn btn-secondary">取消預約</button>
        </div>
      `;
      listEl.appendChild(item);
      // 記錄目前顯示內容的 key，用於輪詢差異比對
      lastMyResvKey = keyOfResv(data);

      return item;
    }

    async function openMyReservations() {
      const listEl = document.getElementById('myresv-list');
      const errEl = document.getElementById('myresv-error');
      listEl.innerHTML = '';
      errEl.textContent = '';

      // Fetch token
      let tokenJson = null;
      try {
        const t = await fetch('/auth/token', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (t.ok) tokenJson = await t.json();
      } catch (_) {}
      if (!tokenJson || !tokenJson.success || !tokenJson.token) {
        errEl.textContent = '請先登入';
      } else {
        try {
          const resp = await fetch('/reservations/top', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          const json = await resp.json();
          if (resp.ok && json && json.success && json.data) {
            const data = json.data || {};
            if (!data.start_time && !data.end_time) {
              errEl.textContent = '目前沒有預約';
            } else {
              const item = renderMyReservation(data, listEl);

              const cancelBtn = item.querySelector('#btnCancelResv');
              cancelBtn.addEventListener('click', async () => {
                errEl.textContent = '';
                try {
                  // Attach Bearer token if available
                  let authHeader = {};
                  try {
                    const tok = await fetch('/auth/token', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (tok.ok) {
                      const tj = await tok.json();
                      if (tj?.success && tj?.token) {
                        authHeader['Authorization'] = 'Bearer ' + tj.token;
                      }
                    }
                  } catch (_) {}
                  const r = await fetch('/reservations/cancel', {
                    method: 'DELETE',
                    headers: {
                      'X-Requested-With': 'XMLHttpRequest',
                      'X-CSRF-TOKEN': csrfToken,
                      ...authHeader
                    },
                    credentials: 'include'
                  });
                  const j = await safeJsonResponse(r);
                  if (j && j.success) {
                    // 後端回傳 { success:true, code, message, data }，即使 data 為 null 也不會拋錯
                    // 根據預約狀態顯示不同的成功訊息
                    const currentStatus = data.status || '';
                    const successMessage = currentStatus === 'CANCELED' ? '取消預約' : '取消成功';
                    showSuccess && showSuccess(successMessage);
                    const safeData = j?.data ?? {};
                    if (safeData.id) {
                      console.log('ID:', safeData.id);
                    }
                    // 關閉「我的預約」模態框
                    document.getElementById('myresv-backdrop').style.display = 'none';
                    document.getElementById('myresv-modal').style.display = 'none';
                    stopMyResvPolling();
                  } else if (r.ok && !j) {
                    // 例如 204 No Content 或非 JSON
                    // 根據預約狀態顯示不同的成功訊息
                    const currentStatus = data.status || '';
                    const successMessage = currentStatus === 'CANCELED' ? '取消預約' : '取消成功';
                    showSuccess && showSuccess(successMessage);
                    // 關閉「我的預約」模態框
                    document.getElementById('myresv-backdrop').style.display = 'none';
                    document.getElementById('myresv-modal').style.display = 'none';
                    stopMyResvPolling();
                  } else {
                    errEl.textContent = (j && j.message) ? j.message : `取消失敗（HTTP ${r.status}）`;
                  }
                } catch (e) {
                  console.error(e);
                  errEl.textContent = '連線失敗';
                }
              });

              // Start polling latest status every 5s while modal is open
              stopMyResvPolling();
              myResvPollTimer = setInterval(async () => {
                try {
                  const r = await fetch('/reservations/top', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                  const j = await r.json();
                  if (r.ok && j && j.success && j.data) {
                    const latest = j.data;
                    const k = keyOfResv(latest);
                    if (k !== lastMyResvKey) {
                      renderMyReservation(latest, listEl);
                    }
                  }
                } catch (_) {}
              }, 5000);
            }
          } else {
            errEl.textContent = (json && json.message) ? json.message : '目前沒有預約';
          }
        } catch (e) {
          errEl.textContent = '讀取失敗';
        }
      }

      document.getElementById('myresv-backdrop').style.display = 'block';
      document.getElementById('myresv-modal').style.display = 'block';
    }
    document.getElementById('myresv-close').addEventListener('click', () => {
      document.getElementById('myresv-backdrop').style.display = 'none';
      document.getElementById('myresv-modal').style.display = 'none';
      stopMyResvPolling();
    });
    document.getElementById('myresv-backdrop').addEventListener('click', () => {
      document.getElementById('myresv-backdrop').style.display = 'none';
      document.getElementById('myresv-modal').style.display = 'none';
      stopMyResvPolling();
    });
  </script>
</body>
</html>