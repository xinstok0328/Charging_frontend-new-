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

    .btn-list {
      background-color: #28a745;
      color: white;
    }
    
  .btn-list:hover {
    background-color: #218838;
    color: white;
  }

  /* 預約列表項目樣式 */
  #resvlist-list > div:hover {
    background: #e2e8f0 !important;
    border-color: #667eea !important;
  }
  
  #resvlist-list > div[data-expanded="true"] {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
      <button onclick="viewUnpaidOrderFromStorage()" class="control-btn" style="background-color: #dc3545; color: white;">
        查看未付款訂單
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
      <button onclick="openReservationList()" class="control-btn btn-list">
        預約列表
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
                <!-- 分鐘選項將由 JavaScript 動態生成 1-60 -->
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
                <!-- 分鐘選項將由 JavaScript 動態生成 1-60 -->
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

    <!-- Reservation List Modal -->
    <div id="resvlist-backdrop" class="reservation-modal-backdrop" style="position: fixed; inset: 0; background: rgba(0,0,0,0.35); display: none; z-index: 1004;"></div>
    <div id="resvlist-modal" class="reservation-modal" role="dialog" aria-modal="true" style="position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 900px; max-width: calc(100% - 32px); height: 85vh; max-height: 85vh; display: none; flex-direction: column; overflow: hidden; z-index: 1005;">
      <header style="padding:14px 16px;border-bottom:1px solid #eee;font-weight:bold;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
        <span>預約列表</span>
        <button id="resvlist-close" class="btn btn-secondary" style="padding:4px 8px;border:none;border-radius:6px;">關閉</button>
      </header>
      
      <!-- 篩選區域 -->
      <div style="padding:16px;border-bottom:1px solid #eee;background:#f8f9fa;flex-shrink:0;">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px;">
          <div>
            <label style="display:block;font-size:12px;color:#4a5568;margin-bottom:4px;">狀態</label>
            <select id="filter-status" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
              <option value="">全部</option>
              <option value="RESERVED">已預約</option>
              <option value="IN_PROGRESS">進行中</option>
              <option value="COMPLETED">已完成</option>
              <option value="CANCELED">已取消</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:12px;color:#4a5568;margin-bottom:4px;">開始時間</label>
            <input type="datetime-local" id="filter-start-time" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" />
          </div>
          <div>
            <label style="display:block;font-size:12px;color:#4a5568;margin-bottom:4px;">結束時間</label>
            <input type="datetime-local" id="filter-end-time" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;" />
          </div>
          <div>
            <label style="display:block;font-size:12px;color:#4a5568;margin-bottom:4px;">每頁顯示</label>
            <select id="filter-limit" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
              <option value="10" selected>10 筆</option>
              <option value="20">20 筆</option>
              <option value="50">50 筆</option>
              <option value="100">100 筆</option>
            </select>
          </div>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end;">
          <button id="btn-filter-apply" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:13px;">套用篩選</button>
          <button id="btn-filter-reset" style="background:#e2e8f0;color:#2d3748;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:13px;">重設</button>
        </div>
      </div>
      
      <div style="flex:1;overflow-y:auto;">
        <div id="resvlist-list" style="display:flex;flex-direction:column;gap:10px;padding:16px;"></div>
        <div id="resvlist-error" style="color:#d63031;font-size:12px;min-height:16px;margin:0 16px;"></div>
      </div>
      
      <div id="resvlist-pagination" style="padding:16px;border-top:1px solid #eee;display:flex;justify-content:center;align-items:center;gap:10px;background:#f8f9fa;flex-shrink:0;"></div>
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
    
    // 預約相關變數
    let myResvPollTimer = null;
    
    // 充電相關變數
    let chargingTimer = null;
    let chargingSession = null;
    let startTime = null;
    
    // 頁面載入時從後端 API 恢復充電會話（使用 session 中的 session_id）
    async function restoreChargingSession() {
      try {
        console.log('🔄 嘗試從後端恢復充電會話（從 session 獲取 session_id）');
        
        // 調用本地 statusIng API，後端會從 session 獲取 session_id
        const response = await fetch('/user/purchase/statusIng', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        
        if (response.ok) {
          const result = await response.json();
          console.log('📥 後端充電狀態回應:', result);
          
          if (result && result.success && result.data) {
            chargingSession = result.data;
            
            if (chargingSession.start_time) {
              startTime = new Date(chargingSession.start_time);
            }
            
            // ✅ 從 /user/purchase/top API 獲取預計結束時間
            console.log('📥 從 /user/purchase/top 獲取預計結束時間...');
            const authToken = localStorage.getItem('auth_token');
            try {
              const topResponse = await fetch('http://120.110.115.126:18081/user/purchase/top', {
                method: 'GET',
                headers: {
                  'Accept': 'application/json',
                  'Authorization': `Bearer ${authToken}`
                },
                mode: 'cors'
              });
              
              if (topResponse.ok) {
                const topResult = await topResponse.json();
                console.log('📥 /user/purchase/top 回應:', topResult);
                
                if (topResult && topResult.success && topResult.data && topResult.data.end_time) {
                  chargingSession.end_time = topResult.data.end_time;
                  console.log('✅ 已設定預計結束時間:', chargingSession.end_time);
                }
              }
            } catch (err) {
              console.warn('⚠️ 無法從 /user/purchase/top 獲取預計結束時間:', err);
            }
            
            console.log('✅ 充電會話已從後端恢復:', chargingSession);
            console.log('⏰ 開始時間已恢復:', startTime);
            
            return true;
          }
        }
        
        console.log('ℹ️ 無可恢復的充電會話');
        return false;
      } catch (error) {
        console.warn('⚠️ 恢復充電會話失敗:', error);
        return false;
      }
    }
    
    // 清除充電會話（只在完成時調用）
    function clearChargingSession() {
      console.log('🗑️ 清除充電會話數據...');
      chargingSession = null;
      startTime = null;
      // session_id 由後端 session 管理，不再需要清除 localStorage
      const sEl = document.getElementById('sessionId');
      if (sEl) sEl.textContent = '-';
      const billEl = document.getElementById('chargingBillId');
      if (billEl) billEl.textContent = '-';
      console.log('✅ 充電會話數據已清除');
    }
    
    // 調試函數：檢查當前狀態
    function debugCurrentState() {
      console.log('🔍 當前狀態調試:');
      console.log('📊 預約輪詢狀態:', myResvPollTimer ? '運行中' : '已停止');
      console.log('⚡ 充電計時器狀態:', chargingTimer ? '運行中' : '已停止');
      console.log('🔋 充電會話:', chargingSession ? '存在' : '不存在');
      console.log('⏰ 開始時間:', startTime ? startTime.toISOString() : '未設定');
      
      // 檢查 localStorage 中的 token
      const authToken = localStorage.getItem('auth_token');
      console.log('🔑 Auth Token:', authToken ? '存在' : '不存在');
      
      return {
        pollingStatus: myResvPollTimer ? 'running' : 'stopped',
        chargingTimerStatus: chargingTimer ? 'running' : 'stopped',
        hasChargingSession: !!chargingSession,
        hasStartTime: !!startTime,
        hasAuthToken: !!authToken,
        timestamp: new Date().toISOString()
      };
    }
    
    // 將調試函數暴露到全局作用域
    window.debugCurrentState = debugCurrentState;
    
    // 檢查充電會話狀態的詳細函數
    window.checkChargingSessionStatus = function() {
      console.log('🔍 檢查充電會話狀態:');
      
      const storedSessionId = localStorage.getItem('charging_session_id');
      const storedStartTime = localStorage.getItem('charging_start_time');
      const storedPricePerHour = localStorage.getItem('charging_price_per_hour');
      
      console.log('📦 localStorage 中的數據:', {
        session_id: storedSessionId,
        start_time: storedStartTime,
        price_per_hour: storedPricePerHour
      });
      
      console.log('💾 記憶體中的數據:', {
        chargingSession: chargingSession,
        chargingSession_session_id: chargingSession?.session_id,
        startTime: startTime
      });
      
      // 檢查充電畫面中的顯示
      const sessionIdEl = document.getElementById('sessionId');
      if (sessionIdEl) {
        console.log('🖥️ 充電畫面中顯示的會話ID:', sessionIdEl.textContent);
      }

      // 更新帳單ID（若有）
      const billEl = document.getElementById('chargingBillId');
      if (billEl) {
        const billIdToShow = chargingSession.charging_bill_id;
        billEl.textContent = billIdToShow || '-';
      }
      
      return {
        hasStoredSession: !!storedSessionId,
        hasMemorySession: !!chargingSession,
        sessionId: storedSessionId || chargingSession?.session_id,
        displayedSessionId: sessionIdEl?.textContent
      };
    };
    
    // 修復充電畫面中的會話ID顯示
    window.fixSessionIdDisplay = function() {
      console.log('🔧 修復充電畫面中的會話ID顯示...');
      
      const sessionIdEl = document.getElementById('sessionId');
      if (!sessionIdEl) {
        console.error('❌ 找不到會話ID顯示元素');
        return false;
      }
      
      // 優先使用記憶體中的 chargingSession
      let sessionIdToShow = null;
      
      if (chargingSession && chargingSession.session_id) {
        sessionIdToShow = chargingSession.session_id;
        console.log('✅ 使用記憶體中的 session_id:', sessionIdToShow);
      } else {
        // 備用方案：從 localStorage 獲取
        sessionIdToShow = localStorage.getItem('charging_session_id');
        console.log('⚠️ 使用 localStorage 中的 session_id (備用方案):', sessionIdToShow);
      }
      
      if (sessionIdToShow) {
        sessionIdEl.textContent = sessionIdToShow;
        console.log('💾 會話ID已修復並顯示:', sessionIdToShow);
        return true;
      } else {
        sessionIdEl.textContent = '-';
        console.warn('⚠️ 無法獲取 sessionId，顯示為 "-"');
        return false;
      }
    };
    
    // 檢查 localStorage 中的所有充電相關數據
    window.checkLocalStorage = function() {
      console.log('🔍 檢查 localStorage 中的所有充電相關數據:');
      
      const chargingSessionId = localStorage.getItem('charging_session_id');
      const chargingStartTime = localStorage.getItem('charging_start_time');
      const chargingPricePerHour = localStorage.getItem('charging_price_per_hour');
      const authToken = localStorage.getItem('auth_token');
      
      console.log('📦 localStorage 中的充電數據:');
      console.log('  - charging_session_id:', chargingSessionId);
      console.log('  - charging_start_time:', chargingStartTime);
      console.log('  - charging_price_per_hour:', chargingPricePerHour);
      console.log('  - auth_token:', authToken ? '存在 (長度: ' + authToken.length + ')' : '不存在');
      
      // 檢查所有 localStorage 項目
      console.log('📋 localStorage 中的所有項目:');
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        const value = localStorage.getItem(key);
        console.log(`  - ${key}:`, value);
      }
      
      return {
        chargingSessionId,
        chargingStartTime,
        chargingPricePerHour,
        authToken: !!authToken,
        totalItems: localStorage.length
      };
    };
    
    // 清除 localStorage 中的充電數據
    window.clearChargingLocalStorage = function() {
      console.log('🗑️ 清除 localStorage 中的充電數據...');
      
      const keysToRemove = [
        'charging_session_id',
        'charging_start_time',
        'charging_price_per_hour'
      ];
      
      keysToRemove.forEach(key => {
        if (localStorage.getItem(key)) {
          localStorage.removeItem(key);
          console.log(`✅ 已清除: ${key}`);
        } else {
          console.log(`ℹ️ 不存在: ${key}`);
        }
      });
      
      console.log('✅ localStorage 充電數據清除完成');
    };
    
    // 手動設置充電會話數據到 localStorage
    window.setChargingSessionToLocalStorage = function(sessionId, startTime, pricePerHour) {
      console.log('💾 手動設置充電會話數據到 localStorage...');
      
      if (sessionId) {
        localStorage.setItem('charging_session_id', sessionId);
        console.log('✅ 已設置 charging_session_id:', sessionId);
      }
      
      if (startTime) {
        localStorage.setItem('charging_start_time', startTime);
        console.log('✅ 已設置 charging_start_time:', startTime);
      }
      
      if (pricePerHour) {
        localStorage.setItem('charging_price_per_hour', pricePerHour);
        console.log('✅ 已設置 charging_price_per_hour:', pricePerHour);
      }
      
      console.log('✅ localStorage 設置完成');
    };
    
    // 全局按鈕處理函數
    async function handleViewChargingClick() {
      console.log('🎯 查看充電狀態按鈕被點擊了！');
      
      const errorElement = document.getElementById('myresv-error');
      if (errorElement) errorElement.textContent = '';
      
      try {
        console.log('查看充電狀態按鈕被點擊');
        
        // 如果已經有充電會話，直接顯示充電畫面
        if (chargingSession) {
          showChargingModal();
          return;
        }
        
        // 獲取當前預約數據
        const authToken = localStorage.getItem('auth_token');
        const response = await fetch('http://120.110.115.126:18081/user/purchase/top', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        const json = await response.json();
        console.log('📥 獲取預約數據:', json);
        
        if (response.ok && json && json.success && json.data) {
          const data = json.data;
          
          // 檢查預約狀態
          console.log('📊 預約狀態:', data.status);
          
          if (data.status === 'COMPLETED' || data.status === 'CANCELED' || data.status === 'CANCELLED') {
            console.log('⚠️ 預約已完成或已取消，無法查看充電狀態');
            if (errorElement) {
              errorElement.textContent = '預約已完成或已取消，無法查看充電狀態';
              errorElement.style.color = 'red';
            }
            return;
          }
          
          // 調用 statusIng API 獲取真實的充電狀態
          try {
            console.log('🔄 調用 status_ing API 獲取充電狀態...');
            console.log('📤 使用 session_id:', data.id || data.session_id);
            
            // 嘗試 GET 請求並在 URL 中傳送 session_id
            const sessionId = data.id || data.session_id;
            const statusUrl = `http://120.110.115.126:18081/user/purchase/status_ing?session_id=${sessionId}&sessionId=${sessionId}`;
            
            console.log('📤 StatusIng 請求 URL:', statusUrl);
            
            const statusResponse = await fetch(statusUrl, {
              method: 'GET',
              headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${authToken}`
              },
              mode: 'cors'
            });
            
            // 檢查回應狀態
            console.log('📡 StatusIng HTTP 狀態碼:', statusResponse.status);
            console.log('📡 StatusIng 回應狀態:', statusResponse.ok ? '成功' : '失敗');
            
            if (!statusResponse.ok) {
              console.error('❌ StatusIng API 回應狀態:', statusResponse.status);
              const errorText = await statusResponse.text();
              console.error('❌ StatusIng 錯誤回應內容:', errorText);
              throw new Error(`StatusIng API 請求失敗: ${statusResponse.status} ${statusResponse.statusText}`);
            }
            
            const statusResult = await statusResponse.json();
            console.log('📥 StatusIng API 回應:', statusResult);
            
            if (statusResult && statusResult.success) {
              // 使用 API 返回的真實數據
              chargingSession = statusResult.data;
              startTime = new Date(chargingSession.start_time);
              
              showChargingModal();
              startChargingTimer();
              
              console.log('✅ 已獲取真實充電狀態:', chargingSession);
            } else {
              console.warn('⚠️ StatusIng API 回應不成功:', statusResult);
              console.warn('⚠️ 錯誤訊息:', statusResult.message);
              
              // 如果訂單無法操作，可能是訂單已完成或狀態不正確
              if (statusResult.message === '訂單無法操作') {
                console.log('🔄 訂單無法操作，可能是已完成狀態，使用模擬數據');
                throw new Error('訂單無法操作 - 可能已完成');
              } else {
                throw new Error(`StatusIng API 失敗: ${statusResult.message}`);
              }
            }
          } catch (apiError) {
            console.error('❌ StatusIng API 錯誤:', apiError);
            
            // 檢查是否是訂單無法操作的錯誤
            if (apiError.message && apiError.message.includes('訂單無法操作')) {
              console.log('🔄 訂單無法操作，可能是已完成或狀態不正確');
              console.log('🔄 建議用戶重新開始新的充電會話');
              
              // 顯示提示信息
              const errorElement = document.getElementById('myresv-error');
              if (errorElement) {
                errorElement.textContent = '訂單無法操作，請重新開始充電';
                errorElement.style.color = 'red';
              }
              
              // 不顯示充電畫面，讓用戶重新開始
              return;
            }
            
            // API 失敗時使用模擬數據作為備用方案
            chargingSession = {
              session_id: data.id || Date.now(),
              start_time: data.start_time,
              end_time: data.end_time,
              price_per_hour: 100,
              duration_min: 60,
              service_fee: 10,
              total_amount: 0,
              discount_amount: 0,
              final_amount: 0
            };
            
            startTime = new Date(data.start_time);
            showChargingModal();
            startChargingTimer();
            
            console.log('🔄 API 失敗，使用模擬充電會話:', chargingSession);
          }
        } else {
          throw new Error('無法獲取預約數據');
        }
        
        // 清除錯誤訊息
        if (errorElement) errorElement.textContent = '';
      } catch (error) {
        console.error('查看充電狀態錯誤:', error);
        if (errorElement) errorElement.textContent = '讀取失敗';
      }
    }
    
    async function handleCancelChargingClick() {
      console.log('🎯 完成充電按鈕被點擊了！');
      
      const errorElement = document.getElementById('myresv-error');
      if (errorElement) errorElement.textContent = '';
      
      try {
        console.log('完成充電按鈕被點擊');
        
        if (confirm('確定要完成充電嗎？完成後將無法恢復。')) {
          console.log('✅ 用戶確認完成充電');
          
          // 獲取當前預約數據
          const authToken = localStorage.getItem('auth_token');
          
          // 從預約模態框中獲取當前預約數據
          const reservationData = await getCurrentReservationData();
          if (!reservationData) {
            console.error('❌ 無法獲取當前預約數據');
            if (errorElement) errorElement.textContent = '無法獲取預約數據';
            return;
          }
          
          // 優先使用 chargingSession 中的 session_id（後端傳來的）
          let sessionId = null;
          if (chargingSession && chargingSession.session_id) {
            sessionId = chargingSession.session_id;
            console.log('✅ 使用 chargingSession 中的 session_id（後端傳來的）:', sessionId);
          } else {
            // 備用方案：從預約數據中獲取
            sessionId = reservationData.id || reservationData.session_id;
            console.log('⚠️ 使用預約數據中的 session_id（備用方案）:', sessionId);
          }
          
          console.log('🔄 調用後端 API 完成充電...');
          console.log('🆔 Session ID:', sessionId);
          console.log('🔑 Auth Token:', authToken ? '存在' : '不存在');
          console.log('📡 API 端點:', 'http://120.110.115.126:18081/user/purchase/end');
          console.log('📊 預約數據:', reservationData);
          
          // 檢查 sessionId 是否有效
          if (!sessionId) {
            console.error('❌ Session ID 無效:', sessionId);
            if (errorElement) errorElement.textContent = 'Session ID 無效';
            return;
          }
          
          // 檢查充電會話是否有效
          if (!chargingSession) {
            console.error('❌ 充電會話無效');
            if (errorElement) errorElement.textContent = '充電會話無效，請重新開始充電';
            return;
          }
          
          // 根據 Swagger API 文檔調整請求參數格式
          // 計算總金額
          const totalAmount = chargingSession.price_per_hour * (chargingSession.duration_min / 60);
          
          const requestBody = {
            sessionId: sessionId,  // 駝峰式寫法
            startTime: new Date(chargingSession.start_time).toISOString(),  // ISO 8601 UTC 格式
            endTime: new Date(chargingSession.end_time).toISOString(),      // ISO 8601 UTC 格式
            pricePerHour: chargingSession.price_per_hour,
            durationMin: chargingSession.duration_min,
            totalAmount: totalAmount
          };
          
          console.log('📤 請求參數:', requestBody);
          console.log('🔍 chargingSession 狀態:', chargingSession);
          console.log('🔍 chargingSession.session_id 類型:', typeof chargingSession?.session_id);
          console.log('🔍 chargingSession.session_id 值:', chargingSession?.session_id);
          console.log('🕐 原始開始時間:', chargingSession.start_time);
          console.log('🕐 轉換後開始時間:', new Date(chargingSession.start_time).toISOString());
          console.log('🕐 原始結束時間:', chargingSession.end_time);
          console.log('🕐 轉換後結束時間:', new Date(chargingSession.end_time).toISOString());
          console.log('💰 計算總金額:', totalAmount);
          
          // 檢查時間是否有效
          const startTimeValid = !isNaN(new Date(chargingSession.start_time).getTime());
          const endTimeValid = !isNaN(new Date(chargingSession.end_time).getTime());
          console.log('🕐 開始時間是否有效:', startTimeValid);
          console.log('🕐 結束時間是否有效:', endTimeValid);
          
          if (!startTimeValid || !endTimeValid) {
            console.error('❌ 時間格式無效，使用當前時間');
            const now = new Date();
            const endTime = new Date(now.getTime() + 60 * 60 * 1000); // 1小時後
            
            requestBody.startTime = now.toISOString();
            requestBody.endTime = endTime.toISOString();
            console.log('🕐 修正後開始時間:', requestBody.startTime);
            console.log('🕐 修正後結束時間:', requestBody.endTime);
          }
          
          // 調用完成充電 API
          const response = await fetch('http://120.110.115.126:18081/user/purchase/end', {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${authToken}`
            },
            mode: 'cors',
            body: JSON.stringify(requestBody)
          });
          
          // 檢查回應狀態
          console.log('📡 HTTP 狀態碼:', response.status);
          console.log('📡 回應狀態:', response.ok ? '成功' : '失敗');
          
          if (!response.ok) {
            console.error('❌ API 回應狀態:', response.status);
            console.error('❌ API 回應 OK:', response.ok);
            const errorText = await response.text();
            console.error('❌ 錯誤回應內容:', errorText);
            throw new Error(`API 請求失敗: ${response.status} ${response.statusText}`);
          }
          
          const result = await response.json();
          console.log('📥 完成充電 API 回應:', result);
          
          if (result && result.success) {
            console.log('✅ 充電已完成');
            
            // 保存 session_id 用於後續處理
            const completedSessionId = result.data?.session_id || sessionId;
            console.log('💾 保存的 session_id:', completedSessionId);
          
          // 清空充電會話
          chargingSession = null;
          startTime = null;
          
          // 關閉預約模態框並刷新預約狀態
          document.getElementById('myresv-backdrop').style.display = 'none';
          document.getElementById('myresv-modal').style.display = 'none';
          stopMyResvPolling();
          
            // 顯示成功訊息 (已移除 alert)
            
            // 刷新地圖和預約狀態，確保用戶可以預約新的充電
            setTimeout(async () => {
              loadMapMarkers();
              
              // 檢查預約狀態是否已更新為完成
              try {
                const statusCheck = await fetch('http://120.110.115.126:18081/user/purchase/top', {
                  method: 'GET',
                  headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authToken}`
                  },
                  mode: 'cors'
                });
                const statusResult = await statusCheck.json();
                console.log('🔍 完成充電後狀態檢查:', statusResult);
                
                if (statusResult.success && (!statusResult.data || statusResult.data.status === 'COMPLETED')) {
                  console.log('✅ 後端狀態已確認更新為完成');
        } else {
                  console.log('⚠️ 後端狀態可能未正確更新:', statusResult.data?.status);
        }
      } catch (error) {
                console.warn('狀態檢查失敗:', error);
              }
              
              console.log('✅ 地圖數據已刷新，用戶可以預約新的充電');
            }, 1000);
            
            console.log('✅ 充電完成，session_id 已保留:', completedSessionId);
          } else {
            console.warn('⚠️ 完成充電失敗:', result);
            console.warn('⚠️ 錯誤訊息:', result.message);
            
            // 檢查是否是訂單無法操作的錯誤
            if (result.message === '訂單無法操作') {
              console.log('🔄 訂單無法操作，可能是已完成或狀態不正確');
              if (errorElement) errorElement.textContent = '訂單無法操作，可能已完成或狀態不正確';
            } else {
              if (errorElement) errorElement.textContent = '完成充電失敗: ' + (result.message || '未知錯誤');
            }
          }
        } else {
          console.log('❌ 用戶取消完成充電');
        }
      } catch (error) {
        console.error('❌ 完成充電錯誤:', error);
        if (errorElement) errorElement.textContent = '連線失敗，請稍後再試';
      }
    }
    
    // 獲取當前預約數據的輔助函數
    async function handleViewChargingClick() {
      console.log('🎯 查看充電狀況按鈕被點擊了！');
      
      try {
        console.log('📥 調用本地 statusIng API（會自動從登入 session 獲取 session_id）');
        
        // 調用本地的 statusIng API（會自動從登入 session 獲取 session_id）
        const statusResponse = await fetch('/user/purchase/statusIng', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });
        
        console.log('📡 StatusIng API HTTP 狀態:', statusResponse.status);
        
        if (statusResponse.ok) {
          const statusResult = await statusResponse.json();
          console.log('📥 StatusIng API 回應:', statusResult);
          
          if (statusResult && statusResult.success && statusResult.data) {
            // 使用真實的充電狀態數據
            chargingSession = statusResult.data;
            startTime = new Date(chargingSession.start_time);
            
            // ✅ 從 /user/purchase/top API 獲取預計結束時間
            console.log('📥 從 /user/purchase/top 獲取預計結束時間...');
            const authToken = localStorage.getItem('auth_token');
            try {
              const topResponse = await fetch('http://120.110.115.126:18081/user/purchase/top', {
                method: 'GET',
                headers: {
                  'Accept': 'application/json',
                  'Authorization': `Bearer ${authToken}`
                },
                mode: 'cors'
              });
              
              if (topResponse.ok) {
                const topResult = await topResponse.json();
                console.log('📥 /user/purchase/top 回應:', topResult);
                
                if (topResult && topResult.success && topResult.data && topResult.data.end_time) {
                  chargingSession.end_time = topResult.data.end_time;
                  console.log('✅ 已設定預計結束時間:', chargingSession.end_time);
                } else {
                  console.warn('⚠️ /user/purchase/top 沒有提供 end_time');
                }
              }
            } catch (err) {
              console.warn('⚠️ 無法從 /user/purchase/top 獲取預計結束時間:', err);
            }
            
            // 顯示充電畫面
            showChargingModal();
            startChargingTimer();
            
            console.log('✅ 已獲取真實充電狀態:', chargingSession);
          } else {
            console.warn('⚠️ StatusIng API 回應格式不正確:', statusResult);
            alert(statusResult.message || '無法獲取充電狀態');
          }
        } else {
          console.error('❌ StatusIng API 請求失敗:', statusResponse.status);
          const errorData = await statusResponse.json().catch(() => ({}));
          
          if (statusResponse.status === 400) {
            alert('找不到充電會話 ID，請先預約並開始充電');
          } else if (statusResponse.status === 401) {
            alert('認證失敗，請重新登入');
          } else {
            alert(errorData.message || '無法獲取充電狀態，請稍後再試');
          }
        }
      } catch (error) {
        console.error('❌ 查看充電狀況錯誤:', error);
        alert('查看充電狀況時發生錯誤：' + error.message);
      }
    }
    
    // 將函數暴露到全局作用域
    window.handleViewChargingClick = handleViewChargingClick;
    
    async function getCurrentReservationData() {
      try {
        const authToken = localStorage.getItem('auth_token');
        const response = await fetch('http://120.110.115.126:18081/user/purchase/top', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        if (response.ok) {
          const result = await response.json();
          if (result.success && result.data) {
            return result.data;
          }
        }
        return null;
      } catch (error) {
        console.error('獲取預約數據失敗:', error);
        return null;
      }
    }

    // 付款相關變數
    let paymentData = null;

    // 顯示付款模態框
    function showPaymentModal() {
      const paymentModal = document.getElementById('paymentModal');
      if (paymentModal) {
        // 計算付款資訊
        calculatePaymentInfo();
        
        paymentModal.style.display = 'flex';
        document.body.classList.add('charging-modal-open');
      }
    }

    // 隱藏付款模態框
    function hidePaymentModal() {
      const paymentModal = document.getElementById('paymentModal');
      if (paymentModal) {
        paymentModal.style.display = 'none';
        document.body.classList.remove('charging-modal-open');
      }
    }

    // 計算付款資訊
    function calculatePaymentInfo() {
      if (!chargingSession || !startTime) {
        // 使用預設值
        paymentData = {
          chargingTime: '00:00:00',
          hourlyRate: '$100/小時',
          serviceFee: '$0',
          totalAmount: '$0.00'
        };
      } else {
        const now = new Date();
        const elapsedMs = now - startTime;
        const elapsedHours = elapsedMs / (1000 * 60 * 60);
        const calculatedTotal = elapsedHours * (chargingSession.price_per_hour || 0);
        const serviceFee = chargingSession.service_fee || 0;
        const totalAmount = calculatedTotal + serviceFee;

        paymentData = {
          chargingTime: formatTime(elapsedMs),
          hourlyRate: `$${chargingSession.price_per_hour || 0}/小時`,
          serviceFee: `$${serviceFee}`,
          totalAmount: `$${totalAmount.toFixed(2)}`
        };
      }

      // 更新付款頁面顯示
      document.getElementById('paymentChargingTime').textContent = paymentData.chargingTime;
      document.getElementById('paymentHourlyRate').textContent = paymentData.hourlyRate;
      document.getElementById('paymentServiceFee').textContent = paymentData.serviceFee;
      document.getElementById('paymentTotalAmount').textContent = paymentData.totalAmount;
    }

    // 確認付款
    function confirmPayment() {
      const selectedMethod = document.querySelector('input[name="paymentMethod"]:checked');
      if (!selectedMethod) {
        alert('請選擇付款方式');
        return;
      }

      const method = selectedMethod.value;
      console.log('選擇的付款方式:', method);

      // 模擬付款處理
      alert(`付款成功！\n付款方式：${method}\n金額：${paymentData.totalAmount}\n感謝您的使用！`);
      
      // 關閉付款模態框
      hidePaymentModal();
      
      // 回到地圖
      window.location.href = '/map';
    }

    // 格式化時間函數
    function formatTime(milliseconds) {
      const totalSeconds = Math.floor(milliseconds / 1000);
      const hours = Math.floor(totalSeconds / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;
      
      return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    // 顯示用戶資訊
    function showUserInfo() {
      alert('用戶資訊功能');
    }

    // 載入附近充電站
    function loadNearbyStations() {
      alert('載入附近充電站功能');
    }

    // 設置充電相關事件監聽器
    function setupChargingEventListeners() {
      // 關閉按鈕（右上角）
      const closeChargingBtn = document.getElementById('closeChargingBtn');
      if (closeChargingBtn) {
        closeChargingBtn.addEventListener('click', function() {
          hideChargingModal();
          // 回到地圖
          window.location.href = '/map';
        });
      }
      
      // 點擊背景關閉
      const chargingModal = document.getElementById('chargingModal');
      if (chargingModal) {
        chargingModal.addEventListener('click', function(e) {
          if (e.target === this) {
            hideChargingModal();
          }
        });
      }

      // 結束充電按鈕
      const endChargingBtn = document.getElementById('endChargingBtn');
      if (endChargingBtn) {
        endChargingBtn.addEventListener('click', endCharging);
      }
    }

    // 設置付款相關事件監聽器
    function setupPaymentEventListeners() {
      // 關閉付款按鈕（右上角）
      const closePaymentBtn = document.getElementById('closePaymentBtn');
      if (closePaymentBtn) {
        closePaymentBtn.addEventListener('click', function() {
          hidePaymentModal();
          // 回到地圖
          window.location.href = '/map';
        });
      }
      
      // 點擊背景關閉付款模態框
      const paymentModal = document.getElementById('paymentModal');
      if (paymentModal) {
        paymentModal.addEventListener('click', function(e) {
          if (e.target === this) {
            hidePaymentModal();
          }
        });
      }
      
      // 確認付款按鈕
      const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');
      if (confirmPaymentBtn) {
        confirmPaymentBtn.addEventListener('click', confirmPayment);
      }
      
      // 未付款訂單模態框事件
      const closeUnpaidOrderBtn = document.getElementById('closeUnpaidOrderBtn');
      if (closeUnpaidOrderBtn) {
        closeUnpaidOrderBtn.addEventListener('click', hideUnpaidOrderModal);
      }
      
      // 點擊背景關閉未付款訂單模態框
      const unpaidOrderModal = document.getElementById('unpaidOrderModal');
      if (unpaidOrderModal) {
        unpaidOrderModal.addEventListener('click', function(e) {
          if (e.target === this) {
            hideUnpaidOrderModal();
          }
        });
      }
      
      // 付款按鈕
      const payUnpaidOrderBtn = document.getElementById('payUnpaidOrderBtn');
      if (payUnpaidOrderBtn) {
        payUnpaidOrderBtn.addEventListener('click', payUnpaidOrder);
      }
    }

    // 顯示充電畫面
    function showChargingModal() {
      const modal = document.getElementById('chargingModal');
      modal.style.display = 'flex';
      
      // 防止頁面滑動
      document.body.classList.add('charging-modal-open');
      
      // 更新充電資訊
      updateChargingInfo();
    }

    // 隱藏充電畫面
    function hideChargingModal() {
      const modal = document.getElementById('chargingModal');
      modal.style.display = 'none';
      
      // 恢復頁面滑動
      document.body.classList.remove('charging-modal-open');
      
      // 停止充電計時器
      stopChargingTimer();
    }

    // 開始充電計時器
    function startChargingTimer() {
      if (chargingTimer) {
        clearInterval(chargingTimer);
      }
      
      chargingTimer = setInterval(updateChargingInfo, 1000);
      updateChargingInfo(); // 立即更新一次
    }

    // 停止充電計時器
    function stopChargingTimer() {
      if (chargingTimer) {
        clearInterval(chargingTimer);
        chargingTimer = null;
      }
    }

    // 更新充電資訊
    function updateChargingInfo() {
      if (!chargingSession || !startTime) return;
      
      const now = new Date();
      const elapsed = Math.floor((now - startTime) / 1000);
      const hours = Math.floor(elapsed / 3600);
      const minutes = Math.floor((elapsed % 3600) / 60);
      const seconds = elapsed % 60;
      
      // 更新開始時間 - 顯示 localStorage 中的開始時間
      const startTimeEl = document.getElementById('currentTime'); // 使用現有的元素ID
      if (startTimeEl) {
        // 優先使用 chargingSession.start_time，如果沒有則從 localStorage 獲取
        const startTimeStr = chargingSession.start_time || localStorage.getItem('charging_start_time');
        if (startTimeStr) {
          const startTime = new Date(startTimeStr);
          const timeStr = startTime.toLocaleTimeString('zh-TW', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
          });
          startTimeEl.textContent = timeStr;
          console.log('🕐 顯示開始時間:', timeStr, '來源:', chargingSession.start_time ? 'chargingSession' : 'localStorage');
        } else {
          // 如果都沒有，顯示當前時間作為備用
          const timeStr = now.toLocaleTimeString('zh-TW', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
          });
          startTimeEl.textContent = timeStr;
          console.log('⚠️ 使用當前時間作為備用:', timeStr);
        }
      }
      
      // 更新結束時間 - 顯示 localStorage 中的結束時間
      const endTimeEl = document.getElementById('endTime');
      if (endTimeEl) {
        // 優先使用 chargingSession.end_time，如果沒有則從 localStorage 獲取
        const endTimeStr = chargingSession.end_time || localStorage.getItem('charging_end_time');
        if (endTimeStr) {
          const endTime = new Date(endTimeStr);
          const timeStr = endTime.toLocaleTimeString('zh-TW', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
          });
          endTimeEl.textContent = timeStr;
          console.log('🕐 顯示結束時間:', timeStr, '來源:', chargingSession.end_time ? 'chargingSession' : 'localStorage');
        } else {
          endTimeEl.textContent = '00:00:00';
          console.log('⚠️ 沒有結束時間，顯示 00:00:00');
        }
      }
      
      // 更新充電時長 - 顯示實際充電的時長（從按下開始充電開始）
      const chargingDurationEl = document.getElementById('chargingDuration');
      if (chargingDurationEl) {
        // 計算實際充電時長（從按下開始充電的實際時間到現在）
        // 使用 startTime 變數，這是按下開始充電時設定的實際時間
        const actualElapsed = Math.floor((now - startTime) / 1000);
        const actualHours = Math.floor(actualElapsed / 3600);
        const actualMinutes = Math.floor((actualElapsed % 3600) / 60);
        const actualSeconds = actualElapsed % 60;
        
        const durationStr = `${actualHours.toString().padStart(2, '0')}:${actualMinutes.toString().padStart(2, '0')}:${actualSeconds.toString().padStart(2, '0')}`;
        chargingDurationEl.textContent = durationStr;
        console.log('⏱️ 顯示充電時長:', durationStr, '實際充電秒數:', actualElapsed);
        console.log('⏱️ 充電開始時間 (startTime):', startTime);
        console.log('⏱️ 當前時間 (now):', now);
        console.log('⏱️ 時差:', actualElapsed, '秒');
      }
      
      // 更新進度條
      const progressFill = document.getElementById('progressFill');
      if (progressFill && chargingSession.end_time) {
        const endTime = new Date(chargingSession.end_time);
        const totalDuration = (endTime - startTime) / 1000;
        const progress = Math.min((elapsed / totalDuration) * 100, 100);
        
        progressFill.style.width = `${progress}%`;
        progressFill.textContent = `${Math.round(progress)}%`;
      }
      
      // 更新會話ID - 統一使用 chargingSession.session_id（從後端 session 獲取）
      const sessionIdEl = document.getElementById('sessionId');
      if (sessionIdEl) {
        // 只使用 chargingSession.session_id（由後端從 session 提供）
        const sessionIdToShow = chargingSession.session_id;
        sessionIdEl.textContent = sessionIdToShow || '-';
        
        // 調試信息
        if (sessionIdToShow) {
          console.log('✅ 會話ID已更新:', sessionIdToShow);
        } else {
          console.warn('⚠️ 無法獲取會話ID，顯示為 "-"');
        }
      }
      
      // 更新帳單ID - 使用 charging_bill_id（與 pile_id 不同）
      const billEl = document.getElementById('chargingBillId');
      if (billEl) {
        const billIdToShow = chargingSession.charging_bill_id;
        if (billIdToShow !== undefined && billIdToShow !== null && billIdToShow !== 0) {
          billEl.textContent = billIdToShow;
        } else {
          billEl.textContent = '-';
        }
      }
      
      // 獲取實際使用的會話ID - 只從 chargingSession 獲取
      const actualSessionId = chargingSession.session_id;
      
      console.log('充電資訊已更新:', {
        elapsed: `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`,
        progress: progressFill ? progressFill.style.width : 'N/A',
        sessionId: actualSessionId,
        chargingBillId: chargingSession.charging_bill_id,
        chargingSession_session_id: chargingSession.session_id
      });
    }

    // 開始充電按鈕點擊處理函數
    async function handleStartChargingClick() {
      console.log('🎯 handleStartChargingClick 被調用');
      
      try {
        // 獲取當前預約數據
        const authToken = localStorage.getItem('auth_token');
        const response = await fetch('http://120.110.115.126:18081/user/purchase/top', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        const json = await response.json();
        console.log('📥 獲取預約數據:', json);
        
        if (response.ok && json && json.success && json.data) {
          const data = json.data;
          
          // 檢查預約狀態
          if (data.status !== 'RESERVED') {
            alert('預約狀態不正確，無法開始充電');
            return;
          }
          
          // 檢查時間是否到了
          const now = new Date();
          const reservationStartTime = new Date(data.start_time);
          if (now < reservationStartTime) {
            alert('預約時間尚未到達，無法開始充電');
            return;
          }
          
          console.log('🔄 調用開始充電 API...');
          console.log('📊 預約數據:', data);
          console.log('🔑 Auth Token:', authToken ? '存在' : '不存在');
          
          // 根據 Swagger API 文檔調整請求參數
          const startRequestBody = {
            pile_id: data.pile_id || data.id,
            pileId: data.pile_id || data.id,  // 備用格式
            start_time: data.start_time,
            startTime: data.start_time,      // 備用格式
            end_time: data.end_time,
            endTime: data.end_time           // 備用格式
          };
          
          console.log('📤 開始充電請求參數:', startRequestBody);
          
          // 調用本地路由（會自動保存 charging_bill_id 到 session）
          const startResponse = await fetch('/user/purchase/start', {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(startRequestBody)
          });
          
          console.log('📡 HTTP 狀態碼:', startResponse.status);
          console.log('📡 回應狀態:', startResponse.ok ? '成功' : '失敗');
          
          if (!startResponse.ok) {
            console.error('❌ 開始充電 API 回應狀態:', startResponse.status);
            const errorText = await startResponse.text();
            console.error('❌ 開始充電錯誤回應內容:', errorText);
            throw new Error(`開始充電 API 請求失敗: ${startResponse.status} ${startResponse.statusText}`);
          }
          
          const result = await startResponse.json();
          console.log('📥 開始充電 API 回應:', result);
          
          if (result && result.success) {
            console.log('✅ 開始充電成功');
            console.log('💾 返回的 session_id:', result.data?.session_id);
            console.log('📊 充電會話數據:', result.data);
            
            // 保存充電會話數據 - 直接使用後端回傳的 data
            chargingSession = result.data;
            
            console.log('🔍 後端回傳的完整數據:', result);
            console.log('🔍 result.data 的所有鍵:', Object.keys(result.data || {}));
            console.log('🔍 result.data 的完整內容:', result.data);
            
            // 確保 session_id 被正確保存
            if (chargingSession.session_id) {
              console.log('✅ session_id 已存在:', chargingSession.session_id);
            } else {
              console.error('❌ chargingSession.session_id 不存在');
            }
            
            // 確保 charging_bill_id 被正確保存
            if (chargingSession.charging_bill_id !== undefined && chargingSession.charging_bill_id !== null) {
              console.log('✅ charging_bill_id 已存在:', chargingSession.charging_bill_id);
            } else {
              console.warn('⚠️ charging_bill_id 不存在或為 null/undefined');
              console.warn('  - result.data.charging_bill_id:', result.data?.charging_bill_id);
              console.warn('  - chargingSession.charging_bill_id:', chargingSession.charging_bill_id);
            }
            
            // 檢查所有必要的欄位是否存在
            console.log('🔍 檢查必要欄位:');
            console.log('  - charging_bill_id:', chargingSession.charging_bill_id, '(類型:', typeof chargingSession.charging_bill_id, ')');
            console.log('  - session_id:', chargingSession.session_id);
            console.log('  - start_time:', chargingSession.start_time);
            console.log('  - end_time:', chargingSession.end_time);
            console.log('  - price_per_hour:', chargingSession.price_per_hour);
            console.log('  - duration_min:', chargingSession.duration_min);
            console.log('  - service_fee:', chargingSession.service_fee);
            console.log('  - total_amount:', chargingSession.total_amount);
            console.log('  - discount_amount:', chargingSession.discount_amount);
            console.log('  - final_amount:', chargingSession.final_amount);
            console.log('  - payment_status:', chargingSession.payment_status);
            console.log('  - pile_response:', chargingSession.pile_response);
            console.log('  - payment_transaction_responses:', chargingSession.payment_transaction_responses);
            
            // 保存預約時間信息到 chargingSession（不覆蓋後端回傳的 charging_bill_id）
            chargingSession.start_time = data.start_time;  // 預約的開始時間
            chargingSession.end_time = data.end_time;      // 預約的結束時間
            chargingSession.pile_id = data.pile_id || data.id;
            
            // 立即更新畫面上的 session 與帳單 ID
            const sEl = document.getElementById('sessionId');
            if (sEl) sEl.textContent = chargingSession.session_id || '-';
            
            const billEl = document.getElementById('chargingBillId');
            if (billEl) {
              // 檢查 charging_bill_id 是否存在且不為 0
              const billId = chargingSession.charging_bill_id;
              if (billId !== undefined && billId !== null && billId !== 0) {
                billEl.textContent = billId;
                console.log('✅ 帳單ID已更新到畫面:', billId);
              } else {
                billEl.textContent = '-';
                console.warn('⚠️ 帳單ID不存在或為0，顯示為 "-"');
              }
            }
            
            // 計算預約時長（分鐘）
            const reservationStartTime = new Date(data.start_time);
            const reservationEndTime = new Date(data.end_time);
            const reservationDurationMinutes = Math.floor((reservationEndTime - reservationStartTime) / (1000 * 60));
            chargingSession.duration_min = reservationDurationMinutes;
            
            console.log('⏰ 保存的時間信息:');
            console.log('  - 預約開始時間:', data.start_time);
            console.log('  - 預約結束時間:', data.end_time);
            console.log('  - 預約時長:', reservationDurationMinutes, '分鐘');
            console.log('  - pile_id:', chargingSession.pile_id);
            
            // 後端已經回傳了所有必要的計費信息，不需要額外調用 tariff API
            console.log('💰 使用後端回傳的計費信息:');
            console.log('  - price_per_hour:', chargingSession.price_per_hour);
            console.log('  - duration_min:', chargingSession.duration_min);
            console.log('  - service_fee:', chargingSession.service_fee);
            console.log('  - total_amount:', chargingSession.total_amount);
            console.log('  - discount_amount:', chargingSession.discount_amount);
            console.log('  - final_amount:', chargingSession.final_amount);
            
            startTime = new Date(chargingSession.start_time);
            
            // 詳細調試 chargingSession 的內容
            console.log('🔍 chargingSession 詳細內容:', chargingSession);
            console.log('🔍 chargingSession.session_id:', chargingSession.session_id);
            console.log('🔍 chargingSession.sessionId:', chargingSession.sessionId);
            console.log('🔍 chargingSession.id:', chargingSession.id);
            console.log('🔍 chargingSession.price_per_hour:', chargingSession.price_per_hour);
            console.log('🔍 chargingSession.duration_min:', chargingSession.duration_min);
            console.log('🔍 chargingSession.service_fee:', chargingSession.service_fee);
            console.log('🔍 chargingSession 的所有鍵:', Object.keys(chargingSession));
            
            // 驗證 session_id 是否正確
            if (chargingSession.session_id && chargingSession.session_id.toString().length >= 10) {
              console.log('✅ session_id 已正確保存:', chargingSession.session_id);
              
              // 保存到 localStorage 以便後續使用（包括刷新頁面後）
              localStorage.setItem('charging_session_id', chargingSession.session_id);
              localStorage.setItem('charging_bill_id', chargingSession.charging_bill_id || '');
              localStorage.setItem('charging_start_time', chargingSession.start_time);
              localStorage.setItem('charging_end_time', chargingSession.end_time);
              localStorage.setItem('charging_price_per_hour', chargingSession.price_per_hour || 20);
              localStorage.setItem('charging_duration_min', chargingSession.duration_min || 0);
              localStorage.setItem('charging_service_fee', chargingSession.service_fee || 0);
              
              console.log('💾 已保存到 localStorage:');
              console.log('  - charging_session_id:', chargingSession.session_id);
              console.log('  - charging_bill_id:', chargingSession.charging_bill_id || '-');
              console.log('  - charging_start_time:', chargingSession.start_time);
              console.log('  - charging_end_time:', chargingSession.end_time);
              console.log('  - charging_price_per_hour:', chargingSession.price_per_hour || 20);
              console.log('  - charging_duration_min:', chargingSession.duration_min || 0);
              console.log('  - charging_service_fee:', chargingSession.service_fee || 0);
              
              console.log('💾 充電會話數據已保存到 localStorage:', {
                session_id: chargingSession.session_id,
                charging_bill_id: chargingSession.charging_bill_id,
                start_time: chargingSession.start_time,
                price_per_hour: chargingSession.price_per_hour
              });
      } else {
              console.error('❌ session_id 保存失敗或格式不正確:', chargingSession.session_id);
              console.error('❌ 原始 API 回應:', result);
            }
            
            // 關閉「我的預約」模態框
            document.getElementById('myresv-backdrop').style.display = 'none';
            document.getElementById('myresv-modal').style.display = 'none';
            stopMyResvPolling();
            
            // 顯示充電畫面
            showChargingModal();
            startChargingTimer();
            
            console.log('充電會話已開始:', chargingSession);
          } else {
            alert(result.message || '開始充電失敗');
          }
        } else {
          alert('無法獲取預約數據');
        }
      } catch (error) {
        console.error('開始充電錯誤:', error);
        alert('連線失敗，請稍後再試');
      }
    }
    
    // 將函數暴露到全局作用域
    window.handleStartChargingClick = handleStartChargingClick;
    
    // 調試函數：檢查 chargingSession 狀態
    window.debugChargingSession = function() {
      console.log('🔍 調試 chargingSession 狀態:');
      console.log('chargingSession:', chargingSession);
      console.log('chargingSession.session_id:', chargingSession?.session_id);
      console.log('startTime:', startTime);
      return chargingSession;
    };
    
    // 驗證 sessionId 是否為正確的充電會話 ID
    window.validateSessionId = function(sessionId) {
      console.log('🔍 驗證 sessionId:', sessionId);
      console.log('類型:', typeof sessionId);
      console.log('長度:', sessionId ? sessionId.toString().length : 0);
      
      if (!sessionId) {
        console.error('❌ sessionId 為空');
        return false;
      }
      
      const sessionIdStr = sessionId.toString();
      if (sessionIdStr.length < 10) {
        console.error('❌ sessionId 太短，可能是預約 ID');
        return false;
      }
      
      if (sessionIdStr === '128') {
        console.error('❌ 這是預約 ID，不是充電會話 ID');
        return false;
      }
      
      console.log('✅ sessionId 格式正確');
      return true;
    };
    
    // 修復 chargingSession 的 session_id 問題
    window.fixChargingSessionId = function() {
      console.log('🔧 嘗試修復 chargingSession.session_id...');
      console.log('🔍 當前 chargingSession:', chargingSession);
      
      if (!chargingSession) {
        console.error('❌ chargingSession 為空，無法修復');
        return false;
      }
      
      // 檢查各種可能的 session_id 欄位名稱
      const possibleSessionIds = [
        chargingSession.session_id,
        chargingSession.sessionId,
        chargingSession.id,
        chargingSession.sessionId,
        chargingSession.session_id
      ];
      
      console.log('🔍 可能的 session_id 值:', possibleSessionIds);
      
      // 找到第一個有效的 session_id
      for (let i = 0; i < possibleSessionIds.length; i++) {
        const sessionId = possibleSessionIds[i];
        if (sessionId && sessionId.toString().length >= 10) {
          console.log(`✅ 找到有效的 session_id: ${sessionId}`);
          chargingSession.session_id = sessionId;
          return true;
        }
      }
      
      console.error('❌ 找不到有效的 session_id');
      return false;
    };
    
    // 檢查所有 API 調用的參數配置
    window.checkApiParameters = function() {
      console.log('🔍 檢查所有 API 調用的參數配置:');
      
      const authToken = localStorage.getItem('auth_token');
      console.log('🔑 Auth Token 狀態:', authToken ? '存在' : '不存在');
      console.log('🔑 Auth Token 長度:', authToken ? authToken.length : 0);
      
      console.log('📋 API 調用配置:');
      console.log('1. 結束充電 API (endCharging):');
      console.log('   - URL: POST http://120.110.115.126:18081/user/purchase/end');
      console.log('   - Headers: Authorization: Bearer ${authToken}');
      console.log('   - Body: session_id, sessionId, id, pile_id, end_time');
      
      console.log('2. 更新狀態為完成 API (updateReservationStatusToCompleted):');
      console.log('   - URL: POST http://120.110.115.126:18081/user/purchase/end');
      console.log('   - Headers: Authorization: Bearer ${authToken}');
      console.log('   - Body: session_id, sessionId, id, end_time');
      
      console.log('3. 更新狀態為過期 API (updateReservationStatusToExpired):');
      console.log('   - URL: DELETE http://120.110.115.126:18081/user/purchase/cancel');
      console.log('   - Headers: Authorization: Bearer ${authToken}');
      console.log('   - Body: session_id');
      
      console.log('4. 查看充電狀態 API (handleViewChargingClick):');
      console.log('   - URL: GET http://120.110.115.126:18081/user/purchase/status_ing');
      console.log('   - Headers: Authorization: Bearer ${authToken}');
      console.log('   - Query: session_id, sessionId');
      
      return {
        hasAuthToken: !!authToken,
        chargingSession: chargingSession,
        sessionId: chargingSession?.session_id
      };
    };
    
    // 強制重新獲取 sessionId
    window.forceGetSessionId = async function() {
      console.log('🔄 強制重新獲取 sessionId...');
      
      if (!chargingSession) {
        console.error('❌ chargingSession 為 null，無法獲取 sessionId');
        console.log('💡 請先按「開始充電」按鈕');
        return null;
      }
      
      // 嘗試從 localStorage 獲取
      const storedSessionId = localStorage.getItem('charging_session_id');
      if (storedSessionId) {
        console.log('✅ 從 localStorage 獲取 sessionId:', storedSessionId);
        chargingSession.session_id = storedSessionId;
        return storedSessionId;
      }
      
      // 嘗試從 chargingSession 的不同欄位獲取
      const possibleSessionIds = [
        chargingSession.session_id,
        chargingSession.sessionId,
        chargingSession.id,
        chargingSession.sessionId
      ];
      
      for (let i = 0; i < possibleSessionIds.length; i++) {
        const sessionId = possibleSessionIds[i];
        if (sessionId && sessionId.toString().length >= 10) {
          console.log(`✅ 找到有效的 sessionId: ${sessionId}`);
          chargingSession.session_id = sessionId;
          localStorage.setItem('charging_session_id', sessionId);
          return sessionId;
        }
      }
      
      console.error('❌ 找不到有效的 sessionId');
      return null;
    };

    // 更新預約狀態為完成的函數
    async function updateReservationStatusToCompleted(sessionId) {
      try {
        const authToken = localStorage.getItem('auth_token');
        console.log('🔄 調用後端 API 更新預約狀態為完成...');
        console.log('📤 使用的 session_id:', sessionId);
        console.log('📤 session_id 類型:', typeof sessionId);
        console.log('📤 session_id 長度:', sessionId ? sessionId.toString().length : 0);
        console.log('🔑 使用的 auth_token:', authToken ? '存在' : '不存在');
        console.log('🔑 auth_token 長度:', authToken ? authToken.length : 0);
        
        // 確認 sessionId 是正確的充電會話 ID
        if (!window.validateSessionId(sessionId)) {
          console.error('❌ sessionId 驗證失敗，無法繼續');
          return;
        }
        
        // 對於時間到了的預約，調用 end API 設為 COMPLETED
        const sessionIdInt = parseInt(sessionId);
        console.log('🔢 session_id 轉換為整數:', sessionIdInt);
        console.log('🔢 session_id 類型:', typeof sessionIdInt);
        
        const response = await fetch(`http://120.110.115.126:18081/user/purchase/end?session_id=${sessionIdInt}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        console.log('📡 End API 回應狀態:', response.status);
        
        const result = await response.json();
        console.log('📥 End API 回應內容:', result);
        
        if (result && result.success) {
          console.log('✅ 後端預約狀態已更新為完成:', result);
          
          // 處理新的回應格式
          const chargingBillId = result.data?.charging_bill_id;
          const paymentStatus = result.data?.payment_status;
          const finalAmount = result.data?.final_amount;
          
          if (chargingBillId) {
            console.log(`💰 充電帳單已生成！帳單編號: ${chargingBillId}`);
            if (paymentStatus === 'UNPAID') {
              console.log('⚠️ 請注意：此充電尚未付款，請盡快完成付款');
            }
          }
          
          // 只有後端確認完成時才清除充電會話
          clearChargingSession();
        } else {
          console.warn('⚠️ 後端預約狀態更新失敗:', result);
          // 即使後端失敗，前端仍然顯示完成狀態
          console.log('ℹ️ 前端仍會顯示完成狀態，但後端可能需要手動處理');
          // 後端失敗時不清除充電會話，保持 sessionId
        }
      } catch (error) {
        console.error('❌ 更新後端狀態錯誤:', error);
        // 即使 API 調用失敗，前端仍然顯示完成狀態
        console.log('ℹ️ API 調用失敗，但前端仍會顯示完成狀態');
      }
    }

    // 更新預約狀態為過期的函數
    async function updateReservationStatusToExpired(sessionId) {
      try {
        const authToken = localStorage.getItem('auth_token');
        console.log('🔄 調用後端 API 更新預約狀態為過期...');
        console.log('📤 使用的 session_id:', sessionId);
        
        // 對於時間到了的 RESERVED 預約，調用 cancel API 設為 EXPIRED
        const sessionIdInt = parseInt(sessionId);
        console.log('🔢 session_id 轉換為整數:', sessionIdInt);
        console.log('🔢 session_id 類型:', typeof sessionIdInt);
        
        const response = await fetch(`http://120.110.115.126:18081/user/purchase/cancel?session_id=${sessionIdInt}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        console.log('📡 Cancel API 回應狀態:', response.status);
        
        const result = await response.json();
        console.log('📥 Cancel API 回應內容:', result);
        
        if (result && result.success) {
          console.log('✅ 後端預約狀態已更新為過期:', result);
        } else {
          console.warn('⚠️ 後端預約狀態更新失敗:', result);
          // 即使後端失敗，前端仍然顯示過期狀態
          console.log('ℹ️ 前端仍會顯示過期狀態，但後端可能需要手動處理');
        }
      } catch (error) {
        console.error('❌ 更新後端狀態錯誤:', error);
        // 即使 API 調用失敗，前端仍然顯示過期狀態
        console.log('ℹ️ API 調用失敗，但前端仍會顯示過期狀態');
      }
    }

    // 結束充電功能
    async function endCharging() {
      if (!chargingSession) {
        alert('沒有進行中的充電會話');
        return;
      }

      console.log('🔍 結束充電 - chargingSession 檢查:', {
        'chargingSession': chargingSession,
        'session_id': chargingSession.session_id,
        'session_id 類型': typeof chargingSession.session_id,
        'session_id 長度': chargingSession.session_id ? chargingSession.session_id.toString().length : 0
      });

      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const authToken = localStorage.getItem('auth_token');
        
        // 獲取實際使用的會話ID
        let actualSessionId = chargingSession.session_id || localStorage.getItem('charging_session_id');
        
        // 準備請求參數 - 確保 session_id 是整數格式
        let sessionIdInt = parseInt(actualSessionId);
        
        console.log('🔄 準備調用完成充電 API...');
        console.log('📤 發送的 session_id:', actualSessionId);
        console.log('📤 發送的 session_id 類型:', typeof actualSessionId);
        console.log('📤 發送的 session_id 長度:', actualSessionId ? actualSessionId.toString().length : 0);
        console.log('📤 完整的 chargingSession:', chargingSession);
        console.log('📦 localStorage 中的 session_id:', localStorage.getItem('charging_session_id'));
        
        // 檢查 chargingSession 中實際可用的欄位
        if (chargingSession) {
          console.log('🔍 chargingSession 的所有鍵:', Object.keys(chargingSession));
          console.log('🔍 chargingSession 的完整內容:', chargingSession);
          
          // 嘗試從 chargingSession 中獲取可能的 ID 欄位
          const possibleIds = [
            chargingSession.id,
            chargingSession.session_id,
            chargingSession.sessionId,
            chargingSession.charging_session_id,
            chargingSession.chargingSessionId,
            chargingSession.order_id,
            chargingSession.orderId,
            chargingSession.reservation_id,
            chargingSession.reservationId
          ];
          
          console.log('🔍 可能的 ID 欄位:', possibleIds);
          
          // 找到第一個有效的 ID
          let foundId = null;
          for (let i = 0; i < possibleIds.length; i++) {
            if (possibleIds[i] && possibleIds[i].toString().length >= 10) {
              foundId = possibleIds[i];
              console.log(`✅ 找到有效的 ID 欄位: ${foundId}`);
              break;
            }
          }
          
          if (foundId) {
            console.log('🔄 使用 chargingSession 中找到的 ID:', foundId);
            // 更新 actualSessionId
            actualSessionId = foundId;
            sessionIdInt = parseInt(foundId);
          }
        }
        console.log('🔑 使用的 auth_token:', authToken ? '存在' : '不存在');
        console.log('🔑 auth_token 長度:', authToken ? authToken.length : 0);
        
        // 小工具：四捨五入到 2 位小數
        const round2 = (n) => Math.round((n + Number.EPSILON) * 100) / 100;
        
        // 由 start_time 算出分鐘數
        function computeDurationMin(startISO, end = new Date()) {
          if (!startISO) return 0;
          const s = new Date(startISO).getTime();
          const e = end.getTime();
          const diffMs = Math.max(0, e - s);
          return Math.floor(diffMs / 60000);
        }
        
        // 使用 chargingSession 中已保存的時間和計費信息，如果沒有則從 localStorage 獲取
        const reservedStartTime = chargingSession.start_time || localStorage.getItem('charging_start_time');
        const reservedEndTime = chargingSession.end_time || localStorage.getItem('charging_end_time');
        const actualEndTime = new Date().toISOString(); // 當前時間作為實際結束時間，使用 ISO 格式
        const pricePerHour = chargingSession.price_per_hour || parseFloat(localStorage.getItem('charging_price_per_hour')) || 20;
        const serviceFee = chargingSession.service_fee || parseFloat(localStorage.getItem('charging_service_fee')) || 0;
        
        // 計算實際充電時長和費用
        const startISO = startTime.toISOString(); // 轉換為 ISO 格式
        const endISO = actualEndTime; // 已經是 ISO 格式
        const durationMin = computeDurationMin(startISO);
        const usageAmount = (pricePerHour / 60) * durationMin; // 費率/分鐘 * 分鐘數
        const totalAmount = round2(usageAmount + Number(serviceFee)); // 加服務費
        const discountAmount = chargingSession.discount_amount || 0;
        const finalAmount = round2(totalAmount - Number(discountAmount)); // 扣折扣
        
        console.log('💰 使用時間和計費信息:');
        console.log('  - 預約開始時間:', reservedStartTime);
        console.log('  - 預計結束時間 (預約時間):', reservedEndTime);
        console.log('  - 實際開始時間 (ISO):', startISO);
        console.log('  - 實際結束時間 (ISO):', endISO);
        console.log('  - 實際充電時長:', durationMin, '分鐘');
        console.log('  - 每小時價格:', pricePerHour, '元');
        console.log('  - 服務費:', serviceFee, '元');
        console.log('  - 使用金額:', usageAmount, '元');
        console.log('  - 總金額:', totalAmount, '元');
        console.log('  - 折扣金額:', discountAmount, '元');
        console.log('  - 最終金額:', finalAmount, '元');
        
        // 簡化請求體 - 只帶 session_id 和 token
        const requestBody = {
          session_id: Number(sessionIdInt)
        };
        
        console.log('🕐 時間格式檢查:');
        console.log('  - startTime (Date物件):', startTime);
        console.log('  - startISO:', startISO);
        console.log('  - endISO:', endISO);
        console.log('  - 時差計算:', durationMin, '分鐘');
        console.log('  - 時差計算 (秒):', Math.floor((new Date(endISO) - new Date(startISO)) / 1000), '秒');
        
        console.log('🔧 正確的 API 調用方式 - Query 參數');
        console.log('📋 請求方式:');
        console.log('  - URL: POST /user/purchase/end?session_id=' + sessionIdInt);
        console.log('  - Headers: Authorization: Bearer token');
        console.log('  - Body: 無 (不使用 JSON body)');
        console.log('  - Query Parameters: session_id=' + sessionIdInt);
        
        console.log('🔍 chargingSession 詳細內容:', chargingSession);
        console.log('🔍 chargingSession.start_time:', chargingSession.start_time);
        console.log('🔍 chargingSession.price_per_hour:', chargingSession.price_per_hour);
        console.log('🔍 chargingSession.duration_min:', chargingSession.duration_min);
        
        console.log('🔢 session_id 轉換為整數:', sessionIdInt);
        console.log('🔢 session_id 類型:', typeof sessionIdInt);
        console.log('📤 Query 參數 session_id:', sessionIdInt);
        
        // 確認 sessionId 是正確的充電會話 ID
        if (!window.validateSessionId(actualSessionId)) {
          console.error('❌ sessionId 驗證失敗，無法繼續');
          alert('充電會話 ID 有問題，請重新開始充電');
          return;
        }
        
        // 在結束充電前，先檢查會話狀態
        console.log('🔍 檢查會話狀態...');
        try {
          const statusUrl = `http://120.110.115.126:18081/user/purchase/status_ing?session_id=${sessionIdInt}&sessionId=${sessionIdInt}`;
          const statusResponse = await fetch(statusUrl, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'Authorization': `Bearer ${authToken}`
            },
            mode: 'cors'
          });
          
          if (statusResponse.ok) {
            const statusResult = await statusResponse.json();
            console.log('📊 會話狀態檢查結果:', statusResult);
            
            if (statusResult && statusResult.success && statusResult.data) {
              const sessionStatus = statusResult.data.status;
              console.log('📋 會話狀態:', sessionStatus);
              
              if (sessionStatus === 'COMPLETED') {
                console.warn('⚠️ 會話已經完成，無需再次結束');
                alert('充電會話已經完成');
                clearChargingSession();
                hideChargingModal();
                return;
              } else if (sessionStatus === 'CANCELED' || sessionStatus === 'CANCELLED') {
                console.warn('⚠️ 會話已經取消，無法結束');
                alert('充電會話已經取消');
                clearChargingSession();
                hideChargingModal();
                return;
              } else if (sessionStatus !== 'IN_PROGRESS') {
                console.warn('⚠️ 會話狀態不允許結束:', sessionStatus);
                alert(`會話狀態不正確 (${sessionStatus})，無法結束充電`);
                return;
              }
              
              console.log('✅ 會話狀態檢查通過，可以結束充電');
            } else {
              console.warn('⚠️ 無法獲取會話狀態，繼續嘗試結束充電');
            }
          } else {
            console.warn('⚠️ 會話狀態檢查失敗，繼續嘗試結束充電');
          }
        } catch (error) {
          console.warn('⚠️ 會話狀態檢查出錯，繼續嘗試結束充電:', error);
        }
        
        const response = await fetch(`http://120.110.115.126:18081/user/purchase/end?session_id=${sessionIdInt}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        console.log('📡 API 回應狀態:', response.status);
        console.log('📡 API 回應 OK:', response.ok);

        const result = await response.json();
        console.log('📥 API 回應內容:', result);
        
        // 詳細的錯誤分析
        if (!response.ok) {
          console.error('❌ API 調用失敗，詳細分析:');
          console.error('📡 HTTP 狀態碼:', response.status);
          console.error('📡 HTTP 狀態文字:', response.statusText);
          console.error('📥 錯誤回應:', result);
          
          // 根據不同的錯誤狀態碼提供不同的處理建議
          if (response.status === 400) {
            console.error('🔍 400 Bad Request 可能的原因:');
            console.error('1. 請求參數格式不正確');
            console.error('2. 會話ID 無效或已過期');
            console.error('3. 用戶沒有權限結束此會話');
            console.error('4. 會話狀態不允許結束');
            console.error('5. 必填參數缺失');
            
            // 嘗試提供解決方案
            console.log('💡 建議解決方案:');
            console.log('1. 檢查會話ID 是否有效');
            console.log('2. 確認用戶有權限操作此會話');
            console.log('3. 檢查會話狀態是否允許結束');
            console.log('4. 聯繫後端開發者確認 API 規格');
          }
          
          alert(`結束充電失敗: ${result.error || '未知錯誤'} (狀態碼: ${response.status})`);
          return;
        }
        
         if (result && result.success) {
           console.log('✅ 後端 API 完成充電成功');
           
           // 保存所有充電完成資料
           const completedSessionId = result.data?.session_id || chargingSession?.session_id;
           const chargingBillId = result.data?.charging_bill_id;
           const paymentStatus = result.data?.payment_status;
           const finalAmount = result.data?.final_amount;
           const pileResponse = result.data?.pile_response;
           const paymentTransactions = result.data?.payment_transaction_responses || [];
           
           console.log('💾 保存的充電完成資料:');
           console.log('  - session_id:', completedSessionId);
           console.log('  - charging_bill_id:', chargingBillId);
           console.log('  - payment_status:', paymentStatus);
           console.log('  - final_amount:', finalAmount);
           console.log('  - pile_response:', pileResponse);
           console.log('  - payment_transactions:', paymentTransactions);
           
           // 保存 charging_bill_id 到 localStorage（如果後端有回傳）
           if (chargingBillId) {
             localStorage.setItem('charging_bill_id', chargingBillId);
             console.log('💾 charging_bill_id 已保存到 localStorage:', chargingBillId);
           }
           
           // 顯示充電完成資訊
           if (chargingBillId && finalAmount) {
             console.log(`💰 充電完成！帳單編號: ${chargingBillId}, 金額: ${finalAmount} 元`);
             
             // 顯示充電樁資訊
             if (pileResponse) {
               console.log('📍 充電樁資訊:');
               console.log('  - 型號:', pileResponse.model);
               console.log('  - 連接器類型:', pileResponse.connector_type);
               console.log('  - 最大功率:', pileResponse.max_kw, 'kW');
               console.log('  - 位置:', pileResponse.location_address);
             }
             
             // 顯示支付交易資訊
             if (paymentTransactions.length > 0) {
               console.log('💳 支付交易資訊:');
               paymentTransactions.forEach((transaction, index) => {
                 console.log(`  交易 ${index + 1}:`);
                 console.log('    - 支付方式:', transaction.payment_method);
                 console.log('    - 提供商:', transaction.provider);
                 console.log('    - 交易ID:', transaction.provider_transaction_id);
                 console.log('    - 金額:', transaction.amount, transaction.currency);
                 console.log('    - 狀態:', transaction.status);
                 console.log('    - 訊息:', transaction.message);
               });
             }
             
             // 可以在此處添加顯示帳單詳情的邏輯
             if (paymentStatus === 'UNPAID') {
               console.log('⚠️ 請注意：此充電尚未付款，請盡快完成付款');
             }
           }
           
           // 只有後端確認完成時才清除充電會話
           clearChargingSession();
           
           hideChargingModal();
           
           // 關閉預約模態框並顯示「目前無預約」
           document.getElementById('myresv-backdrop').style.display = 'none';
           document.getElementById('myresv-modal').style.display = 'none';
           stopMyResvPolling();
           
           // 直接顯示「目前無預約」
           const listEl = document.getElementById('myresv-list');
           if (listEl) {
             listEl.innerHTML = '';
             const noReservationDiv = document.createElement('div');
             noReservationDiv.style.textAlign = 'center';
             noReservationDiv.style.padding = '20px';
             noReservationDiv.style.color = '#666';
             noReservationDiv.innerHTML = '目前無預約';
             listEl.appendChild(noReservationDiv);
           }
           
           // 顯示成功訊息 (已移除 alert)
           
           // 刷新地圖和預約狀態，確保用戶可以預約新的充電
           setTimeout(async () => {
             loadMapMarkers();
             
             // 檢查預約狀態是否已更新為完成
             try {
               const statusCheck = await fetch('http://120.110.115.126:18081/user/purchase/top', {
                 method: 'GET',
                 headers: {
                   'Accept': 'application/json',
                   'Authorization': `Bearer ${authToken}`
                 },
                 mode: 'cors'
               });
               const statusResult = await statusCheck.json();
               console.log('🔍 完成充電後狀態檢查:', statusResult);
               
               if (statusResult.success && (!statusResult.data || statusResult.data.status === 'COMPLETED')) {
                 console.log('✅ 後端狀態已確認更新為完成');
         } else {
                 console.log('⚠️ 後端狀態可能未正確更新:', statusResult.data?.status);
         }
      } catch (error) {
               console.warn('狀態檢查失敗:', error);
             }
             
             console.log('✅ 地圖數據已刷新，用戶可以預約新的充電');
           }, 1000);
           
           console.log('✅ 充電已完成，狀態已更新為完成，session_id 已保留:', completedSessionId);
         } else {
           alert('完成充電失敗: ' + (result.message || '未知錯誤'));
           console.error('完成充電 API 錯誤:', result);
         }
      } catch (error) {
        console.error('完成充電錯誤:', error);
        alert('完成充電失敗，請稍後再試');
      }
    }

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
      
      // 恢復充電會話（如果存在）
      const hasRestoredSession = restoreChargingSession();
      if (hasRestoredSession) {
        console.log('🔄 充電會話已從 localStorage 恢復');
      }
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
      setupChargingEventListeners();
      setupPaymentEventListeners();
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
      
      // Populate minute options (1-60 minutes)
      populateMinuteOptions('resv-start-minute');
      populateMinuteOptions('resv-end-minute');
      
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
      
      // 計算「下一分鐘的開始」：當前分鐘 + 1，秒數設為 0
      const nextMinuteStart = new Date(now);
      nextMinuteStart.setSeconds(0, 0); // 秒數和毫秒設為 0
      nextMinuteStart.setMinutes(nextMinuteStart.getMinutes() + 1); // 加 1 分鐘
      
      // Add hour options
      for (let hour = 0; hour < 24; hour++) {
        const option = document.createElement('option');
        option.value = String(hour).padStart(2, '0');
        option.textContent = String(hour).padStart(2, '0');
        
        // Disable hours that are too early (只檢查小時，不檢查分鐘)
        if (isToday && hour < nextMinuteStart.getHours()) {
          option.disabled = true;
        }
        
        hourSelect.appendChild(option);
      }
    }

    // 新增：生成分鐘選項的函數 (1-60分鐘)
    function populateMinuteOptions(minuteSelectId) {
      const minuteSelect = document.getElementById(minuteSelectId);
      
      // Clear existing options
      minuteSelect.innerHTML = '';
      
      // Add minute options (1-60)
      for (let minute = 0; minute < 60; minute++) {
        const option = document.createElement('option');
        option.value = String(minute).padStart(2, '0');
        option.textContent = String(minute).padStart(2, '0');
        minuteSelect.appendChild(option);
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
    
    // Helper function to manage error box visibility
    function showReservationError(message) {
      const errEl = document.getElementById('resv-error');
      if (message) {
        errEl.textContent = message;
        errEl.style.display = 'block';
      } else {
        errEl.textContent = '';
        errEl.style.display = 'none';
      }
    }
    
    function hideReservationError() {
      showReservationError('');
    }
    
    // Format display time from backend to Taiwan timezone
    function formatDisplayTime(timeString) {
      if (!timeString) return '-';
      
      try {
        // 查看預約時直接顯示後端返回的時間（不加 8 小時）
        const backendDate = new Date(timeString);
        
        // 格式化為 YYYY-MM-DD HH:mm 格式
        const year = backendDate.getFullYear();
        const month = String(backendDate.getMonth() + 1).padStart(2, '0');
        const day = String(backendDate.getDate()).padStart(2, '0');
        const hours = String(backendDate.getHours()).padStart(2, '0');
        const minutes = String(backendDate.getMinutes()).padStart(2, '0');
        
        const formattedTime = `${year}-${month}-${day} ${hours}:${minutes}`;
        
        console.log('Time formatting:', {
          input: timeString,
          backend: backendDate.toISOString(),
          display: formattedTime,
          note: 'Direct display of backend time (no timezone adjustment)'
        });
        
        return formattedTime;
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
        
        hideReservationError();

        document.getElementById('reservation-backdrop').style.display = 'block';
        document.getElementById('reservation-modal').style.display = 'block';
      }
    });

    document.getElementById('resv-cancel').addEventListener('click', () => {
      // Hide error box when closing modal
      hideReservationError();
      document.getElementById('reservation-backdrop').style.display = 'none';
      document.getElementById('reservation-modal').style.display = 'none';
    });
    document.getElementById('reservation-backdrop').addEventListener('click', () => {
      // Hide error box when closing modal
      hideReservationError();
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
      hideReservationError();

      if (!pileId || !startStr || !endStr) {
        showReservationError('請完整填寫');
        console.log('Form validation failed:', { pileId, startStr, endStr });
        return;
      }

      // Convert local datetime to API format (YYYY-MM-DD HH:mm:ss)
      // Convert local datetime to API format (後端期望的格式)
      const toApiFormat = (local) => {
        // 直接使用表單中已經調整好的時間（包含自動調整的結束時間）
        const taiwanTime = local + '+08:00'; // 添加台灣時區標識
        const utcTime = new Date(taiwanTime);
        
        // 預約時先加 8 小時再傳給後端
        const adjustedTime = new Date(utcTime.getTime() + (8 * 60 * 60 * 1000));
        
        // 轉換為後端期望的格式 (移除毫秒和時區標識符，符合 java.time.LocalDateTime)
        return adjustedTime.toISOString().replace(/\.\d{3}Z$/, '');
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
        timeFormat: {
          userSelectedStart: startStr,
          userSelectedEnd: endStr,
          apiStart: toApiFormat(startStr),
          apiEnd: toApiFormat(endStr),
          timezoneHandling: "Add 8 hours when making reservation, direct display when viewing",
          note: "Reservation: +8 hours to backend, View: direct backend time"
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

      // Use getAuthHeaders() function (same as other API calls)
      const authHeaders = getAuthHeaders();
      if (!authHeaders['Authorization']) {
        showReservationError('請先登入再預約');
        return;
      }

      // 檢查 token 是否有效
      const token = localStorage.getItem('auth_token');
      if (!token) {
        showReservationError('請先登入再預約');
        return;
      }

      // 檢查 token 是否過期
      try {
        const parts = token.split('.');
        if (parts.length !== 3) {
          throw new Error('Invalid token format');
        }
        const payload = JSON.parse(atob(parts[1]));
        const isExpired = Date.now() > payload.exp * 1000;
        
        if (isExpired) {
          console.error('❌ Token 已過期');
          localStorage.removeItem('auth_token');
          showReservationError('登入已過期，請重新登入');
          return;
        }
        
        console.log('✅ Token 有效，可以進行預約');
      } catch (error) {
        console.error('❌ Token 格式錯誤:', error);
        localStorage.removeItem('auth_token');
        showReservationError('登入狀態異常，請重新登入');
        return;
      }

      // Local pre-checks per minimal rules
      const toDate = (s) => new Date(s);
      const sd = toDate(startStr);
      const ed = toDate(endStr);
      if (!(sd instanceof Date) || isNaN(sd) || !(ed instanceof Date) || isNaN(ed)) {
        showReservationError('傳入的時間格式錯誤，或是日期時間不符合標準');
        return;
      }
      if (ed <= sd) {
        showReservationError('預約結束時間必須晚於開始時間');
        console.log('End time validation failed:', { start: startStr, end: endStr });
        return;
      }
      
      // Check minimum advance reservation time (只要分鐘比當前時間大就可以，不考慮秒數)
      const now = new Date();
      
      // 計算「下一分鐘的開始」：當前分鐘 + 1，秒數設為 0
      const nextMinuteStart = new Date(now);
      nextMinuteStart.setSeconds(0, 0); // 秒數和毫秒設為 0
      nextMinuteStart.setMinutes(nextMinuteStart.getMinutes() + 1); // 加 1 分鐘
      
      // 如果預約時間小於「下一分鐘的開始」，則拒絕
      if (sd < nextMinuteStart) {
        showReservationError('預約的開始時間必須大於當前分鐘（不考慮秒數）');
        return;
      }
      
      // Check bookable date range (14 days from today)
      const maxBookableDate = new Date(now.getTime() + 14 * 24 * 60 * 60 * 1000); // 14 days from now
      if (sd > maxBookableDate) {
        showReservationError('超出可預約的日期範圍（只能預約今天起14天內的時間）');
        return;
      }
      // 移除時長驗證，允許任意時長預約
      // const minutesBetween = Math.round((ed - sd) / 60000);
      // if (minutesBetween < 30 || minutesBetween > 240) {
      //   showReservationError('預約的時長不符合規則（小於30分鐘或大於4小時）');
      //   return;
      // }
      
      // 移除時間粒度驗證，允許任意分鐘選擇
      // const startMinutes = sd.getMinutes();
      // const endMinutes = ed.getMinutes();
      // if (startMinutes % 15 !== 0 || endMinutes % 15 !== 0) {
      //   showReservationError('預約的時間沒有對齊時間粒度（只能選00、15、30、45分）');
      //   return;
      // }

      const submitBtn = document.getElementById('resv-submit');
      submitBtn.disabled = true;
      try {
        // Guard: ensure no active reservation (use external API)
        try {
          const authToken = localStorage.getItem('auth_token');
          const chk = await fetch('http://120.110.115.126:18081/user/purchase/top', { 
            method: 'GET', 
            headers: {
              'Accept': 'application/json',
              'Authorization': `Bearer ${authToken}`
            },
            mode: 'cors'
          });
          const chkJson = await chk.json();
          
          console.log('Current reservation check:', {
            status: chk.status,
            data: chkJson,
            hasActiveReservation: chkJson?.data?.status
          });
          
          if (chk.ok && chkJson && chkJson.success && chkJson.data && chkJson.data.status) {
            const data = chkJson.data;
            const now = new Date();
            const reservationEndTime = new Date(data.end_time);
            
            // 檢查是否為活躍預約（根據狀態，不考慮時間）
            const isActiveReservation = (data.status === 'RESERVED' || data.status === 'IN_PROGRESS');
            
            if (isActiveReservation) {
              showReservationError('您已有進行中的預約，如需新的預約請先取消');
              submitBtn.disabled = false;
              return;
            } else {
              console.log('ℹ️ 現有預約已過期或完成，可以進行新預約');
            }
          }
        } catch (error) {
          console.warn('Reservation check failed:', error);
        }

        const requestBody = {
          pile_id: pileId,
          start_time: toApiFormat(startStr),
          end_time: toApiFormat(endStr)
        };
        
        console.log('Sending reservation request:', requestBody);
        console.log('API endpoint: /user/purchase/reserve');
        console.log('Request headers:', Object.assign({
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Idempotency-Key': uuidv4()
        }, authHeaders));
        console.log('Auth token:', localStorage.getItem('auth_token') ? '存在' : '不存在');
        console.log('Auth token 內容:', localStorage.getItem('auth_token'));
        console.log('authHeaders 內容:', authHeaders);
        console.log('Pile ID:', pileId);
        console.log('Start time:', startStr, '->', toApiFormat(startStr));
        console.log('End time:', endStr, '->', toApiFormat(endStr));
        
        const resp = await fetch('http://120.110.115.126:18081/user/purchase/reserve', {
          method: 'POST',
          headers: Object.assign({}, authHeaders, {
            'Idempotency-Key': uuidv4()
          }),
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
          
          // Map backend codes to UI (based on Swagger API documentation)
          if (resp.status === 400) msg = '請求格式錯誤，請檢查輸入資料';
          if (resp.status === 401) msg = '請先登入再預約';
          if (resp.status === 409) msg = '該時段不可用或與其他預約衝突';
          if (resp.status === 500) msg = '伺服器內部錯誤，請稍後再試';
          
          // Handle specific error codes from API response
          if (json?.code === 40001) msg = '傳入的時間格式錯誤，或是日期時間不符合標準';
          if (json?.code === 40002) msg = '預約的開始時間早於現在時間';
          if (json?.code === 40003) msg = '預約結束時間比開始時間還早';
          if (json?.code === 40004) msg = '超出可預約的日期範圍（預設是今天起14天內）';
          if (json?.code === 40005) msg = '預約的時間格式錯誤';
          // 移除時長驗證錯誤碼
          // if (json?.code === 40006) msg = '預約的時長不符合規則（小於30分鐘或大於4小時）';
          if (json?.code === 40007) msg = '嘗試跨日預約，但系統設定不允許';
          if (json?.code === 40008) msg = '預約時間和其他已存在的預約衝突';
          if (json?.code === 40009) msg = '您已預約，如需新的預約請先取消';
          
          showReservationError(msg);
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

        // 直接在本頁等待至預約開始時間，然後跳轉到充電動畫頁
        try {
          const reservation = {
            id: (json && (json.id || (json.data && (json.data.id || json.data.reservationId)))) || Date.now(),
            pile_id: pileId,
            start_time: toIsoZ(startStr),
            end_time: toIsoZ(endStr),
            status: 'confirmed'
          };
          localStorage.setItem('activeReservation', JSON.stringify(reservation));

          const startMs = new Date(reservation.start_time).getTime();
          const nowMs = Date.now();
          const delay = Math.max(0, startMs - nowMs);

          // 可選：顯示簡短提示（不影響原本畫面）
          try {
            const tip = document.createElement('div');
            tip.textContent = '預約已確認，將於預約時間自動開始充電...';
            tip.style.position = 'fixed';
            tip.style.bottom = '16px';
            tip.style.right = '16px';
            tip.style.padding = '10px 12px';
            tip.style.background = 'rgba(43, 122, 11, 0.9)';
            tip.style.color = '#fff';
            tip.style.borderRadius = '6px';
            tip.style.zIndex = '2000';
            document.body.appendChild(tip);
            setTimeout(() => { try { document.body.removeChild(tip); } catch(_){} }, 4000);
          } catch(_) {}

          setTimeout(() => {
            window.location.href = '/charging-animation?id=' + reservation.id;
          }, delay);
        } catch (_) {
          // 若寫入失敗，至少不阻擋原本流程
        }
      } catch (e) {
        console.error('Reservation error:', e);
        showReservationError('連線失敗，請稍後再試');
      } finally {
        submitBtn.disabled = false;
      }
    }));
    // ========== end Reservation modal logic ==========

    // ========== My Reservations (view & cancel) ==========
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

    async function renderMyReservation(data, listEl) {
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

      // 檢查預約時間和狀態邏輯
      const now = new Date();
      const reservationStartTime = new Date(data.start_time);
      const reservationEndTime = new Date(data.end_time);
      
      // 檢查狀態，如果是 COMPLETED、CANCELLED、EXPIRED 等狀態，直接顯示「目前無預約」
      if (data.status === 'COMPLETED' || data.status === 'CANCELED' || data.status === 'CANCELLED' || data.status === 'EXPIRED') {
        listEl.innerHTML = '';
        const noReservationDiv = document.createElement('div');
        noReservationDiv.style.textAlign = 'center';
        noReservationDiv.style.padding = '20px';
        noReservationDiv.style.color = '#666';
        noReservationDiv.innerHTML = '目前無預約';
        listEl.appendChild(noReservationDiv);
        return noReservationDiv;
      }
      
      // 優先使用後端返回的狀態，只在必要時才進行前端判斷
      let actualStatus = data.status;
      let shouldShowReservation = (data.status === 'RESERVED' || data.status === 'IN_PROGRESS');
      
      // 只有在後端狀態不明確時才進行前端時間判斷
      console.log('🔍 時間檢查:', {
        '當前時間': now.toISOString(),
        '結束時間': reservationEndTime.toISOString(),
        '時間已過': now >= reservationEndTime,
        '後端狀態': data.status,
        'RESERVED時間已過': data.status === 'RESERVED' && now >= reservationEndTime,
        'IN_PROGRESS時間已過': data.status === 'IN_PROGRESS' && now >= reservationEndTime
      });
      
      // 處理 RESERVED 狀態時間到了的情況
      if (data.status === 'RESERVED' && now >= reservationEndTime) {
        // 後端說是 RESERVED 但時間已過，設為 EXPIRED
        actualStatus = 'EXPIRED';
        shouldShowReservation = false;
        console.log('✅ 後端狀態為 RESERVED 但時間已過，前端標記為 EXPIRED');
        console.log('⏰ 當前時間:', now.toISOString(), '結束時間:', reservationEndTime.toISOString());
        
        // 調用後端 API 更新狀態為過期
        // 優先使用從「開始充電」API 獲取的 session_id
        let sessionIdToUse = null;
        
        if (chargingSession && chargingSession.session_id) {
          sessionIdToUse = chargingSession.session_id;
          console.log('✅ 使用「開始充電」API 回傳的 session_id:', sessionIdToUse);
        } else if (data.session_id) {
          sessionIdToUse = data.session_id;
          console.log('⚠️ 使用預約數據的 session_id (備用方案):', sessionIdToUse);
        } else {
          sessionIdToUse = data.id;
          console.log('❌ 只能使用預約 ID (不建議):', sessionIdToUse);
        }
        
        console.log('🔄 準備調用 updateReservationStatusToExpired，使用 session_id:', sessionIdToUse);
        console.log('🔍 數據結構檢查:', {
          'data.id': data.id,
          'data.session_id': data.session_id,
          'chargingSession.session_id': chargingSession?.session_id,
          '最終使用的ID': sessionIdToUse
        });
        
        await updateReservationStatusToExpired(sessionIdToUse);
        
        // 顯示「目前無預約」
        listEl.innerHTML = '';
        const noReservationDiv = document.createElement('div');
        noReservationDiv.style.textAlign = 'center';
        noReservationDiv.style.padding = '20px';
        noReservationDiv.style.color = '#666';
        noReservationDiv.innerHTML = '目前無預約';
        listEl.appendChild(noReservationDiv);
        
        return noReservationDiv;
        
      } else if (data.status === 'IN_PROGRESS' && now >= reservationEndTime) {
        // 後端說是 IN_PROGRESS 但時間已過，設為 COMPLETED
        actualStatus = 'COMPLETED';
        shouldShowReservation = false;
        console.log('✅ 後端狀態為 IN_PROGRESS 但時間已過，前端標記為 COMPLETED');
        console.log('⏰ 當前時間:', now.toISOString(), '結束時間:', reservationEndTime.toISOString());
        
        // 調用後端 API 更新狀態為完成
        // 優先使用從「開始充電」API 獲取的 session_id
        let sessionIdToUse = null;
        
        if (chargingSession && chargingSession.session_id) {
          sessionIdToUse = chargingSession.session_id;
          console.log('✅ 使用「開始充電」API 回傳的 session_id:', sessionIdToUse);
        } else if (data.session_id) {
          sessionIdToUse = data.session_id;
          console.log('⚠️ 使用預約數據的 session_id (備用方案):', sessionIdToUse);
        } else {
          sessionIdToUse = data.id;
          console.log('❌ 只能使用預約 ID (不建議):', sessionIdToUse);
        }
        
        console.log('🔄 準備調用 updateReservationStatusToCompleted，使用 session_id:', sessionIdToUse);
        console.log('🔍 數據結構檢查:', {
          'data.id': data.id,
          'data.session_id': data.session_id,
          'chargingSession.session_id': chargingSession?.session_id,
          '最終使用的ID': sessionIdToUse
        });
        
        await updateReservationStatusToCompleted(sessionIdToUse);
        
        // 顯示「目前無預約」
        listEl.innerHTML = '';
        const noReservationDiv = document.createElement('div');
        noReservationDiv.style.textAlign = 'center';
        noReservationDiv.style.padding = '20px';
        noReservationDiv.style.color = '#666';
        noReservationDiv.innerHTML = '目前無預約';
        listEl.appendChild(noReservationDiv);
        
        return noReservationDiv;
        
      } else if (data.status === 'RESERVED' && now >= reservationStartTime) {
        // 時間已到但保持 RESERVED 狀態，等待用戶手動開始充電
        actualStatus = 'RESERVED';
        console.log('⏰ 後端狀態為 RESERVED 且時間已到，保持 RESERVED 狀態等待手動開始');
        console.log('⏰ 當前時間:', now.toISOString(), '開始時間:', reservationStartTime.toISOString());
      }
      
      // 如果後端已經明確返回 IN_PROGRESS 或 COMPLETED，就信任後端
      if (data.status === 'IN_PROGRESS' || data.status === 'COMPLETED') {
        actualStatus = data.status;
        shouldShowReservation = (data.status === 'IN_PROGRESS');
        console.log('✅ 信任後端狀態:', actualStatus);
      }
      
      const isTimeToStart = now >= reservationStartTime && actualStatus === 'RESERVED';
      const isInProgress = actualStatus === 'IN_PROGRESS';
      const isCompleted = actualStatus === 'COMPLETED';
      const isExpired = actualStatus === 'EXPIRED';
      
      // 調試：顯示實際狀態值
      console.log('🔍 預約狀態調試:');
      console.log('⏰ 當前時間:', now.toISOString());
      console.log('📅 預約開始時間:', reservationStartTime.toISOString());
      console.log('✅ 時間已到:', now >= reservationStartTime);
      console.log('📊 實際狀態:', actualStatus);
      console.log('🎯 是否顯示開始充電按鈕:', isTimeToStart);
      console.log('📊 原始狀態:', data.status);
      console.log('📊 實際狀態:', actualStatus);
      console.log('📊 當前時間:', now.toISOString());
      console.log('📊 開始時間:', reservationStartTime.toISOString());
      console.log('📊 結束時間:', reservationEndTime.toISOString());
      console.log('📊 是否顯示預約:', shouldShowReservation);
      console.log('📊 是否為 IN_PROGRESS:', isInProgress);
      console.log('📊 是否為 COMPLETED:', isCompleted);

      if (!shouldShowReservation) {
        // 不顯示預約時，只顯示「目前無預約」
        item.innerHTML = `
          <div style="text-align: center; padding: 40px 20px; color: #666; font-size: 18px; font-weight: 500;">
            目前無預約
          </div>
        `;
      } else {
        // 顯示預約資訊
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
        <div>狀態：<span id="myresv-status">${actualStatus || ''}</span></div>
        <div style="margin-top:8px;display:flex;gap:8px;">
            ${isInProgress ? 
              `<button id="btnViewCharging" class="btn btn-success" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);" onclick="handleViewChargingClick()">查看充電狀況</button>` :
            (actualStatus === 'CANCELED' || actualStatus === 'CANCELLED') ?
            `<div style="color: #ef4444; font-weight: 600; padding: 8px 16px; background: #fef2f2; border-radius: 6px; border: 1px solid #fecaca;">預約已取消</div>` :
            `<button id="btnCancelResv" class="btn btn-secondary">取消預約</button>
               ${isTimeToStart ? `<button id="btnStartCharging" class="btn btn-danger" style="background: linear-gradient(135deg, #e53e3e, #c53030); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(229, 62, 62, 0.3);" onclick="console.log('開始充電按鈕被點擊 - onclick'); handleStartChargingClick();" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(229, 62, 62, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(229, 62, 62, 0.3)'">開始充電</button>` : ''}`
          }
        </div>
      `;
      }
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
          const authToken = localStorage.getItem('auth_token');
          console.log('🔍 獲取預約數據 - Auth Token:', authToken ? '存在' : '不存在');
          console.log('🔍 API 端點:', 'http://120.110.115.126:18081/user/purchase/top');
          
          const resp = await fetch('http://120.110.115.126:18081/user/purchase/top', {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'Authorization': `Bearer ${authToken}`
            },
            mode: 'cors'
          });
          
          console.log('📡 API 回應狀態:', resp.status);
          console.log('📡 API 回應 OK:', resp.ok);
          
          const json = await resp.json();
          console.log('📥 API 回應內容:', json);
          if (resp.ok && json && json.success && json.data) {
            const data = json.data || {};
            console.log('🔍 預約數據狀態檢查:', {
              '狀態': data.status,
              '開始時間': data.start_time,
              '結束時間': data.end_time,
              '有時間數據': !!(data.start_time && data.end_time)
            });
            
            if (!data.start_time && !data.end_time) {
              errEl.textContent = '目前沒有預約';
            } else {
              const item = await renderMyReservation(data, listEl);
              
                // 確保模態框顯示
                document.getElementById('myresv-backdrop').style.display = 'block';
                document.getElementById('myresv-modal').style.display = 'block';
              
              // 如果 renderMyReservation 返回「目前無預約」元素，不需要進一步處理
              if (!item || item.innerHTML.includes('目前無預約')) {
                console.log('✅ 顯示「目前無預約」狀態');
                return;
              }

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
                  const r = await fetch('http://120.110.115.126:18081/user/purchase/cancel', {
                    method: 'DELETE',
                    headers: getAuthHeaders(),
                    mode: 'cors'
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

              // 為「刪除預約」按鈕添加事件監聽器
              const deleteReservationBtn = item.querySelector('#btnDeleteReservation');
              if (deleteReservationBtn) {
                deleteReservationBtn.addEventListener('click', async function() {
                  if (confirm('確定要刪除這筆預約嗎？刪除後可以重新預約。')) {
                    try {
                      const authToken = localStorage.getItem('auth_token');
                      const response = await fetch('http://120.110.115.126:18081/user/purchase/cancel', {
                        method: 'DELETE',
                        headers: getAuthHeaders()
                      });
                      
                      const result = await response.json();
                      if (result && result.success) {
                        alert('預約已刪除，可以重新預約');
                        // 關閉預約模態框
                        document.getElementById('myresv-backdrop').style.display = 'none';
                        document.getElementById('myresv-modal').style.display = 'none';
                        stopMyResvPolling();
                      } else {
                        alert('刪除失敗: ' + (result.message || '未知錯誤'));
                      }
                    } catch (error) {
                      console.error('刪除預約錯誤:', error);
                      alert('刪除失敗，請稍後再試');
                    }
                  }
                });
              }

              // 為「查看充電狀態」按鈕添加事件監聽器（如果按鈕存在）
              const viewChargingBtn = item.querySelector('#btnViewCharging');
              const cancelChargingBtn = item.querySelector('#btnCancelCharging');
              
              console.log('🔍 按鈕調試信息:');
              console.log('📋 查看充電狀態按鈕:', viewChargingBtn);
              console.log('📋 完成充電按鈕:', cancelChargingBtn);
              console.log('📋 查看充電狀態按鈕是否存在:', !!viewChargingBtn);
              console.log('📋 完成充電按鈕是否存在:', !!cancelChargingBtn);
              console.log('📋 實際狀態:', actualStatus);
              console.log('📋 是否為 IN_PROGRESS:', isInProgress);
              console.log('📋 item.innerHTML:', item.innerHTML);
              
              if (viewChargingBtn) {
                console.log('✅ 查看充電狀態按鈕存在，添加事件監聽器');
                viewChargingBtn.addEventListener('click', async function() {
                  console.log('🎯 查看充電狀態按鈕被點擊了！');
                  alert('查看充電狀態按鈕被點擊了！');
                  const errorElement = document.getElementById('myresv-error');
                  if (errorElement) errorElement.textContent = '';
                  
                  try {
                    console.log('查看充電狀態按鈕被點擊');
                    
                    // 如果已經有充電會話，直接顯示充電畫面
                    if (chargingSession) {
                      showChargingModal();
                      return;
                    }
                    
                    // 使用模擬數據顯示充電畫面
                    chargingSession = {
                      session_id: data.id || Date.now(),
                      start_time: data.start_time,
                      end_time: data.end_time,
                      price_per_hour: 100,
                      duration_min: 60,
                      service_fee: 10,
                      total_amount: 0,
                      discount_amount: 0,
                      final_amount: 0
                    };
                    
                    startTime = new Date(data.start_time);
                    showChargingModal();
                    startChargingTimer();
                    
                    console.log('🔄 使用模擬充電會話:', chargingSession);
                    
                    // 清除錯誤訊息
                    if (errorElement) errorElement.textContent = '';
                  } catch (error) {
                    console.error('查看充電狀態錯誤:', error);
                    if (errorElement) errorElement.textContent = '讀取失敗';
                  }
                });
              } else {
                console.log('❌ 查看充電狀態按鈕不存在');
              }

              // 為「完成充電」按鈕添加事件監聽器（如果按鈕存在）
              if (cancelChargingBtn) {
                console.log('✅ 完成充電按鈕存在，添加事件監聽器');
                cancelChargingBtn.addEventListener('click', async function() {
                  console.log('🎯 完成充電按鈕被點擊了！');
                  const errorElement = document.getElementById('myresv-error');
                  if (errorElement) errorElement.textContent = '';
                  
                  try {
                    console.log('完成充電按鈕被點擊');
                    
                    if (confirm('確定要完成充電嗎？完成後將無法恢復。')) {
                      const authToken = localStorage.getItem('auth_token');
                      const sessionId = data.id || data.session_id;
                      
                      console.log('🔄 調用後端 API 完成充電...');
                      console.log('🆔 Session ID:', sessionId);
                      console.log('🔑 Auth Token:', authToken ? '存在' : '不存在');
                      console.log('📡 API 端點:', 'http://120.110.115.126:18081/user/purchase/end');
                      console.log('📤 請求參數:', { session_id: sessionId });
                      
                      // 調用完成充電 API
                      const response = await fetch('http://120.110.115.126:18081/user/purchase/end', {
                        method: 'POST',
                        headers: {
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'Authorization': `Bearer ${authToken}`
                        },
                        mode: 'cors',
                        body: JSON.stringify({
                          session_id: sessionId
                        })
                      });
                      
                      const result = await response.json();
                      console.log('📥 完成充電 API 回應:', result);
                      console.log('📡 HTTP 狀態碼:', response.status);
                      console.log('📡 回應狀態:', response.ok ? '成功' : '失敗');
                      
                      if (result && result.success) {
                        console.log('✅ 充電已完成');
                        
                        // 保存 session_id 用於後續處理
                        const completedSessionId = result.data?.session_id || sessionId;
                        console.log('💾 保存的 session_id:', completedSessionId);
                        
                        // 清空充電會話
                        chargingSession = null;
                        startTime = null;
                        
                        // 關閉預約模態框並刷新預約狀態
                        document.getElementById('myresv-backdrop').style.display = 'none';
                        document.getElementById('myresv-modal').style.display = 'none';
                        stopMyResvPolling();
                        
                        // 顯示成功訊息 (已移除 alert)
                        
                        // 刷新地圖和預約狀態，確保用戶可以預約新的充電
                        setTimeout(async () => {
                          loadMapMarkers();
                          
                          // 檢查預約狀態是否已更新為完成
                          try {
                            const statusCheck = await fetch('http://120.110.115.126:18081/user/purchase/top', {
                              method: 'GET',
                              headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${authToken}`
                              },
                              mode: 'cors'
                            });
                            const statusResult = await statusCheck.json();
                            console.log('🔍 完成充電後狀態檢查:', statusResult);
                            
                            if (statusResult.success && (!statusResult.data || statusResult.data.status === 'COMPLETED')) {
                              console.log('✅ 後端狀態已確認更新為完成');
                      } else {
                              console.log('⚠️ 後端狀態可能未正確更新:', statusResult.data?.status);
                            }
                          } catch (error) {
                            console.warn('狀態檢查失敗:', error);
                          }
                          
                          console.log('✅ 地圖數據已刷新，用戶可以預約新的充電');
                        }, 1000);
                        
                        console.log('✅ 充電完成，session_id 已保留:', completedSessionId);
                      } else {
                        console.warn('⚠️ 完成充電失敗:', result);
                        if (errorElement) errorElement.textContent = '完成充電失敗: ' + (result.message || '未知錯誤');
                      }
                    }
                  } catch (error) {
                    console.error('❌ 完成充電錯誤:', error);
                    if (errorElement) errorElement.textContent = '連線失敗，請稍後再試';
                  }
                });
              } else {
                console.log('❌ 完成充電按鈕不存在');
              }

              // 為「開始充電」按鈕添加事件監聽器（如果按鈕存在）
              const startChargingBtn = item.querySelector('#btnStartCharging');
              console.log('🔍 查找開始充電按鈕:', startChargingBtn);
              console.log('🔍 按鈕是否存在:', !!startChargingBtn);
              if (startChargingBtn) {
                console.log('✅ 找到開始充電按鈕，添加事件監聽器');
                startChargingBtn.addEventListener('click', async () => {
                  errEl.textContent = '';
                  try {
                    console.log('開始充電按鈕被點擊');
                    
                    // 檢查預約狀態
                    if (data.status !== 'RESERVED') {
                      errEl.textContent = '預約狀態不正確，無法開始充電';
                      return;
                    }
                    
                    // 檢查時間是否到了
                    const now = new Date();
                    const reservationStartTime = new Date(data.start_time);
                    if (now < reservationStartTime) {
                      errEl.textContent = '預約時間尚未到達，無法開始充電';
                      return;
                    }
                    
                    // 調用開始充電 API
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const authToken = localStorage.getItem('auth_token');
                    
                    if (!authToken) {
                      errEl.textContent = '請先登入';
                      return;
                    }
                    
                    console.log('🔄 調用開始充電 API...');
                    console.log('📊 預約數據:', data);
                    console.log('🔑 Auth Token:', authToken ? '存在' : '不存在');
                    console.log('📡 API 端點:', 'http://120.110.115.126:18081/user/purchase/start');
                    
                    // 根據 Swagger API 文檔調整請求參數
                    const startRequestBody = {
                      pile_id: data.pile_id || data.id,
                      pileId: data.pile_id || data.id,  // 備用格式
                      start_time: data.start_time,
                      startTime: data.start_time,      // 備用格式
                      end_time: data.end_time,
                      endTime: data.end_time           // 備用格式
                    };
                    
                    console.log('📤 開始充電請求參數:', startRequestBody);
                    
                    // 調用本地路由（會自動保存 charging_bill_id 到 session）
                    const response = await fetch('/user/purchase/start', {
                      method: 'POST',
                      headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                      },
                      body: JSON.stringify(startRequestBody)
                    });
                    
                    // 檢查開始充電 API 回應
                    console.log('📡 HTTP 狀態碼:', response.status);
                    console.log('📡 回應狀態:', response.ok ? '成功' : '失敗');
                    
                    if (!response.ok) {
                      console.error('❌ 開始充電 API 回應狀態:', response.status);
                      console.error('❌ 開始充電 API 回應 OK:', response.ok);
                      const errorText = await response.text();
                      console.error('❌ 開始充電錯誤回應內容:', errorText);
                      throw new Error(`開始充電 API 請求失敗: ${response.status} ${response.statusText}`);
                    }
                    
                    const result = await response.json();
                    console.log('📥 開始充電 API 回應:', result);
                    
                    if (result && result.success) {
                      console.log('✅ 開始充電成功');
                      console.log('💾 返回的 session_id:', result.data?.session_id);
                      console.log('📊 充電會話數據:', result.data);
                      
                      // 保存充電會話數據
                      chargingSession = result.data;
                      
                      // 記錄新欄位
                      console.log('🔍 開始充電 API 新欄位:');
                      console.log('  - charging_bill_id:', chargingSession.charging_bill_id);
                      console.log('  - payment_status:', chargingSession.payment_status);
                      console.log('  - pile_response:', chargingSession.pile_response);
                      console.log('  - payment_transaction_responses:', chargingSession.payment_transaction_responses);
                      
                      startTime = new Date(chargingSession.start_time);
                      
                      // 關閉「我的預約」模態框
                      document.getElementById('myresv-backdrop').style.display = 'none';
                      document.getElementById('myresv-modal').style.display = 'none';
                      stopMyResvPolling();
                      
                      // 顯示充電畫面
                      showChargingModal();
                      startChargingTimer();
                      
                      console.log('充電會話已開始:', chargingSession);
                    } else {
                      errEl.textContent = result.message || '開始充電失敗';
                    }
                  } catch (error) {
                    console.error('開始充電錯誤:', error);
                    errEl.textContent = '連線失敗，請稍後再試';
                  }
                });
              }

              // Start polling latest status every 3s while modal is open (更頻繁的更新)
              stopMyResvPolling();
              myResvPollTimer = setInterval(async () => {
                try {
                  console.log('🔄 輪詢預約狀態...');
                  const authToken = localStorage.getItem('auth_token');
                  const r = await fetch('http://120.110.115.126:18081/user/purchase/top', { 
                    method: 'GET', 
                    headers: {
                      'Accept': 'application/json',
                      'Authorization': `Bearer ${authToken}`
                    },
                    mode: 'cors'
                  });
                  
                  console.log('📡 輪詢回應狀態:', r.status);
                  
                  if (r.ok) {
                  const j = await r.json();
                    console.log('📥 輪詢數據:', j);
                    
                    if (j && j.success && j.data) {
                    const latest = j.data;
                    const k = keyOfResv(latest);
                      console.log('🔍 輪詢狀態比較:', {
                        currentKey: k,
                        lastKey: lastMyResvKey,
                        hasChanged: k !== lastMyResvKey
                      });
                      
                    if (k !== lastMyResvKey) {
                        console.log('✅ 預約狀態已更新，重新渲染');
                      const item = await renderMyReservation(latest, listEl);
                        // 如果預約已完成（返回「目前無預約」元素），停止輪詢
                      if (!item || item.innerHTML === '目前無預約') {
                          console.log('🏁 預約已完成，停止輪詢');
                          stopMyResvPolling();
                        return;
                      }
                      lastMyResvKey = k;
                      } else {
                        console.log('⏸️ 預約狀態無變化');
                      }
                    } else {
                      console.log('⚠️ 輪詢無數據或失敗:', j);
                    }
                  } else {
                    console.log('❌ 輪詢請求失敗:', r.status);
                  }
                } catch (error) {
                  console.error('❌ 輪詢錯誤:', error);
                }
              }, 3000); // 改為每3秒輪詢一次
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
    
    // 將函數暴露到全局作用域
    window.openMyReservations = openMyReservations;
    
    // 預約列表功能
    let currentPage = 1;
    let currentFilters = {};
    
    async function loadReservationList(page = 1, filters = {}) {
      const listEl = document.getElementById('resvlist-list');
      const errEl = document.getElementById('resvlist-error');
      const paginationEl = document.getElementById('resvlist-pagination');
      
      errEl.textContent = '';
      listEl.innerHTML = '';
      paginationEl.innerHTML = '';
      
      try {
        const authToken = localStorage.getItem('auth_token');
        
        // 建立查詢參數
        const params = new URLSearchParams({
          page: page,
          limit: filters.limit || '10',
          order: 'desc',
          sort: 'id'
        });
        
        // 加入篩選參數
        if (filters.status) {
          params.append('status', filters.status);
        }
        if (filters.start_time) {
          params.append('start_time', filters.start_time);
        }
        if (filters.end_time) {
          params.append('end_time', filters.end_time);
        }
        
        console.log('📥 載入預約列表，參數:', params.toString());
        
        const response = await fetch(`http://120.110.115.126:18081/user/purchase/list?${params.toString()}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        const json = await response.json();
        console.log('📥 預約列表 API 回應:', json);
        
        if (response.ok && json && json.success) {
          if (json.data && json.data.records && json.data.records.length > 0) {
            // 顯示預約列表
            json.data.records.forEach((record) => {
              const item = document.createElement('div');
              item.style.cssText = 'border: 1px solid #e2e8f0; border-radius: 8px; background: #f8f9fa;';
              
              const startTime = new Date(record.start_time);
              const endTime = new Date(record.end_time);
              const statusColors = {
                'RESERVED': '#667eea',
                'IN_PROGRESS': '#10b981',
                'COMPLETED': '#6c757d',
                'CANCELED': '#ef4444',
                'CANCELLED': '#ef4444',
                'EXPIRED': '#f59e0b'
              };
              
              const statusColors_zh = {
                'RESERVED': '已預約',
                'IN_PROGRESS': '進行中',
                'COMPLETED': '已完成',
                'CANCELED': '已取消',
                'CANCELLED': '已取消',
                'EXPIRED': '已過期'
              };
              
              const formatTime = (timeStr) => {
                const date = new Date(timeStr);
                return date.toLocaleString('zh-TW', { 
                  year: 'numeric', month: '2-digit', day: '2-digit',
                  hour: '2-digit', minute: '2-digit', second: '2-digit',
                  hour12: true
                });
              };
              
              // 計算時長
              const durationMinutes = record.duration_min || Math.floor((endTime - startTime) / (1000 * 60));
              const hours = Math.floor(durationMinutes / 60);
              const minutes = durationMinutes % 60;
              const durationText = hours > 0 ? `${hours}小時${minutes}分鐘` : `${minutes}分鐘`;
              
              // 顯示更多資訊
              item.innerHTML = `
                <div style="padding: 16px;">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="flex: 1;">
                      <div style="font-weight: 600; color: #2d3748; margin-bottom: 6px; font-size: 15px;">ID: ${record.id}</div>
                      <div style="font-size: 13px; color: #4a5568; margin-bottom: 4px;">
                        <span style="font-weight: 500;">時間：</span>${formatTime(record.start_time)} - ${formatTime(record.end_time)}
                      </div>
                      <div style="font-size: 13px; color: #4a5568; margin-bottom: 4px;">
                        <span style="font-weight: 500;">時長：</span>${durationText}
                      </div>
                      ${record.pile_response ? `
                        <div style="font-size: 13px; color: #4a5568; margin-bottom: 4px;">
                          <span style="font-weight: 500;">充電樁：</span>${record.pile_response.model || '未知型號'} (${record.pile_response.max_kw || 0}kW)
                        </div>
                        <div style="font-size: 12px; color: #718096;">
                          <span style="font-weight: 500;">位置：</span>${record.pile_response.location_address || '未知位置'}
                        </div>
                      ` : ''}
                    </div>
                    <div style="padding: 6px 12px; border-radius: 6px; background: ${statusColors[record.status] || '#6c757d'}; color: white; font-size: 12px; font-weight: 700; white-space: nowrap; margin-left: 12px;">
                      ${statusColors_zh[record.status] || record.status}
                    </div>
                  </div>
                </div>
              `;
              
              listEl.appendChild(item);
            });
            
            // 顯示分頁資訊和控制
            if (json.data.page && json.data.page.total_page > 1) {
              const page = json.data.page.current_page;
              const totalPage = json.data.page.total_page;
              const totalCount = json.data.page.total_count;
              
              paginationEl.innerHTML = `
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:center;">
                  <button onclick="loadReservationList(1, currentFilters)" style="padding:8px 12px;border:1px solid #d1d5db;background:white;border-radius:6px;cursor:pointer;font-size:13px;" ${page === 1 ? 'disabled' : ''}">« 首頁</button>
                  <button onclick="loadReservationList(${page - 1}, currentFilters)" style="padding:8px 12px;border:1px solid #d1d5db;background:white;border-radius:6px;cursor:pointer;font-size:13px;" ${page === 1 ? 'disabled' : ''}">‹ 上一頁</button>
                  <div style="font-size:13px;color:#4a5568;">
                    第 <strong>${page}</strong> 頁 / 共 ${totalPage} 頁 (總計 ${totalCount} 筆)
                  </div>
                  <button onclick="loadReservationList(${page + 1}, currentFilters)" style="padding:8px 12px;border:1px solid #d1d5db;background:white;border-radius:6px;cursor:pointer;font-size:13px;" ${page === totalPage ? 'disabled' : ''}>下一頁 ›</button>
                  <button onclick="loadReservationList(${totalPage}, currentFilters)" style="padding:8px 12px;border:1px solid #d1d5db;background:white;border-radius:6px;cursor:pointer;font-size:13px;" ${page === totalPage ? 'disabled' : ''}">末頁 »</button>
                </div>
              `;
            } else if (json.data.page) {
              paginationEl.innerHTML = `
                <div style="font-size:13px;color:#4a5568;text-align:center;">
                  共 ${json.data.page.total_count} 筆資料
                </div>
              `;
            }
          } else {
            // 沒有預約記錄
            listEl.innerHTML = `
              <div style="text-align: center; padding: 40px; color: #718096;">
                目前沒有預約記錄
              </div>
            `;
          }
        } else if (json && json.success && json.data && Object.keys(json.data).length === 0) {
          // 報錯的情況：data 是空物件
          console.warn('⚠️ API 返回空的 data 物件（錯誤情況）');
          errEl.textContent = json.message || '無法載入預約列表';
          listEl.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #d63031;">
              無法載入預約列表：${json.message || '未知錯誤'}
            </div>
          `;
        } else {
          errEl.textContent = (json && json.message) ? json.message : '無法載入預約列表';
          listEl.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #d63031;">
              ${errEl.textContent}
            </div>
          `;
        }
      } catch (e) {
        console.error('❌ 載入預約列表錯誤:', e);
        errEl.textContent = '讀取失敗';
        listEl.innerHTML = `
          <div style="text-align: center; padding: 40px; color: #d63031;">
            載入失敗：${e.message}
          </div>
        `;
      }
      
      currentPage = page;
    }
    
    // 主函數：開啟預約列表
    async function openReservationList() {
      // 顯示模態框
      document.getElementById('resvlist-backdrop').style.display = 'block';
      document.getElementById('resvlist-modal').style.display = 'flex';  // 改為 flex 以支援垂直佈局
      
      // 載入第一頁
      currentPage = 1;
      await loadReservationList(1, currentFilters);
    }
    
    // 檢查付款狀態
    // 由 purchaseId 解析 charging_bill_id
    async function resolveBillId(purchaseId) {
      try {
        const authToken = localStorage.getItem('auth_token');
        const resp = await fetch(`http://120.110.115.126:18081/user/purchase/bill_info/${purchaseId}`, {
          method: 'GET',
          headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${authToken}` },
          mode: 'cors'
        });
        const json = await resp.json();
        if (resp.ok && json && json.success && json.data && json.data.charging_bill_id) {
          return json.data.charging_bill_id;
        }
      } catch (e) {
        console.warn('⚠️ 解析 charging_bill_id 失敗:', e);
      }
      return null;
    }

    async function checkPaymentStatus(billId, purchaseId, statusDomId) {
      console.log('🔍 檢查付款狀態，billId:', billId, 'purchaseId:', purchaseId);
      // 若沒有 billId，嘗試解析
      if (!billId && purchaseId) {
        billId = await resolveBillId(purchaseId);
      }
      const statusEl = document.getElementById(statusDomId || (billId ? `payment-status-${billId}` : `payment-status-p${purchaseId}`));
      if (!billId) {
        if (statusEl) statusEl.innerHTML = '💳 <span style="color:#718096;">暫無訂單 ID</span>';
        return;
      }
      
      try {
        const authToken = localStorage.getItem('auth_token');
        const response = await fetch(`http://120.110.115.126:18081/user/purchase/unpaid_bill?charging_bill_id=${billId}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        const data = await response.json();
        console.log('📥 付款狀態 API 回應:', data);
        
        // statusEl 已在前方取得
        if (statusEl) {
          if (response.ok && data && data.success && data.data) {
            const bill = data.data;
            if (bill.payment_status === 'PAID') {
              statusEl.innerHTML = '💳 <span style="color: #10b981;">已付款</span>';
            } else if (bill.payment_status === 'UNPAID') {
              statusEl.innerHTML = '💳 <span style="color: #ef4444;">未付款</span>';
            }
          } else if (data && data.message && data.message.includes('未結清')) {
            statusEl.innerHTML = '💳 <span style="color: #ef4444;">未付款</span>';
          } else {
            statusEl.innerHTML = '💳 <span style="color: #10b981;">已付款</span>';
          }
        }
      } catch (error) {
        console.error('❌ 檢查付款狀態失敗:', error);
        if (statusEl) {
          statusEl.innerHTML = '💳 <span style="color: #f59e0b;">檢查失敗</span>';
        }
      }
    }
    
    // 查看未付款訂單
    async function viewUnpaidBill(billId, purchaseId) {
      console.log('🔍 查看未付款訂單，billId:', billId, 'purchaseId:', purchaseId);
      if (!billId && purchaseId) {
        billId = await resolveBillId(purchaseId);
      }
      if (!billId) {
        alert('無法取得充電訂單 ID，請稍後再試');
        return;
      }
      
      try {
        const authToken = localStorage.getItem('auth_token');
        const response = await fetch(`http://120.110.115.126:18081/user/purchase/unpaid_bill?charging_bill_id=${billId}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${authToken}`
          },
          mode: 'cors'
        });
        
        const data = await response.json();
        console.log('📥 未付款訂單 API 回應:', data);
        console.log('📥 API 回應中的 charging_bill_id:', data?.data?.charging_bill_id);
        console.log('📥 傳入的 billId 參數:', billId);
        
        if (response.ok && data && data.success && data.data) {
          const bill = data.data;
          
          // 如果 API 回應中沒有 charging_bill_id，使用傳入的 billId
          if (!bill.charging_bill_id || bill.charging_bill_id === 0 || bill.charging_bill_id === '0') {
            console.log('⚠️ API 回應中沒有 charging_bill_id，使用傳入的 billId:', billId);
            bill.charging_bill_id = billId;
          }
          
          // 構建充電樁資訊
          let pileInfo = '無';
          if (bill.pile_response) {
            const pile = bill.pile_response;
            pileInfo = `
型號: ${pile.model || '-'}
連接器類型: ${pile.connector_type || '-'}
最大功率: ${pile.max_kw || '-'} kW
韌體版本: ${pile.firmware_version || '-'}
地址: ${pile.location_address || '-'}
座標: (${pile.lat || '-'}, ${pile.lng || '-'})`;
          }
          
          // 構建支付交易資訊
          let paymentInfo = '無';
          if (bill.payment_transaction_responses && bill.payment_transaction_responses.length > 0) {
            paymentInfo = bill.payment_transaction_responses.map((txn, idx) => `
交易 #${idx + 1}
  付款方式: ${txn.payment_method || '-'}
  提供者: ${txn.provider || '-'}
  交易 ID: ${txn.provider_transaction_id || '-'}
  金額: ${txn.amount || '-'} ${txn.currency || '-'}
  狀態: ${txn.status || '-'}
  訊息: ${txn.message || '-'}
  請求時間: ${txn.request_time ? new Date(txn.request_time).toLocaleString('zh-TW') : '-'}
  完成時間: ${txn.completed_time ? new Date(txn.completed_time).toLocaleString('zh-TW') : '-'}
  備註: ${txn.meta || '-'}`).join('\n');
          }
          
          // 確保 charging_bill_id 正確顯示（即使值為 0 或 null 也嘗試顯示 billId）
          const displayBillId = (bill.charging_bill_id !== undefined && bill.charging_bill_id !== null && bill.charging_bill_id !== 0 && bill.charging_bill_id !== '0') 
            ? bill.charging_bill_id 
            : (billId && billId !== '0') ? billId : '-';
          
          // 顯示未付款訂單模態框
          showUnpaidOrderModal(bill, displayBillId);
          
          // 如果有充電樁資訊，記錄到 console
          if (bill.pile_response) {
            console.log('📍 充電樁資訊:', bill.pile_response);
          }
          
          // 如果有支付交易資訊，記錄到 console
          if (bill.payment_transaction_responses && bill.payment_transaction_responses.length > 0) {
            console.log('💳 支付交易資訊:', bill.payment_transaction_responses);
          }
        } else {
          // 如果回傳「未結清」訊息，顯示未付款
          if (data && data.message && data.message.includes('未結清')) {
            alert('此訂單尚有未結清款項');
          } else {
            alert(data?.message || '此訂單已結清或無需付款');
          }
        }
      } catch (error) {
        console.error('❌ 獲取未付款訂單失敗:', error);
        alert('連線失敗，請稍後再試');
      }
    }
    
    // 顯示未付款訂單模態框
    function showUnpaidOrderModal(bill, displayBillId) {
      // 填充訂單資訊
      document.getElementById('unpaidBillId').textContent = displayBillId;
      document.getElementById('unpaidSessionId').textContent = bill.session_id || '-';
      document.getElementById('unpaidStartTime').textContent = bill.start_time ? new Date(bill.start_time).toLocaleString('zh-TW') : '-';
      document.getElementById('unpaidEndTime').textContent = bill.end_time ? new Date(bill.end_time).toLocaleString('zh-TW') : '-';
      document.getElementById('unpaidDuration').textContent = bill.duration_min ? `${bill.duration_min} 分鐘` : '-';
      document.getElementById('unpaidPricePerHour').textContent = bill.price_per_hour ? `$${bill.price_per_hour}` : '-';
      document.getElementById('unpaidServiceFee').textContent = bill.service_fee !== undefined && bill.service_fee !== null ? `$${bill.service_fee}` : '-';
      document.getElementById('unpaidTotalAmount').textContent = bill.total_amount !== undefined && bill.total_amount !== null ? `$${bill.total_amount}` : '-';
      document.getElementById('unpaidDiscountAmount').textContent = bill.discount_amount !== undefined && bill.discount_amount !== null ? `$${bill.discount_amount}` : '-';
      document.getElementById('unpaidFinalAmount').textContent = bill.final_amount !== undefined && bill.final_amount !== null ? `$${bill.final_amount}` : '-';
      
      // 填充充電樁資訊
      const pileInfoDiv = document.getElementById('unpaidPileInfo');
      if (bill.pile_response) {
        const pile = bill.pile_response;
        document.getElementById('unpaidPileModel').textContent = pile.model || '-';
        document.getElementById('unpaidPileConnector').textContent = pile.connector_type || '-';
        document.getElementById('unpaidPileMaxKw').textContent = pile.max_kw !== undefined && pile.max_kw !== null ? `${pile.max_kw} kW` : '-';
        document.getElementById('unpaidPileAddress').textContent = pile.location_address || '-';
        pileInfoDiv.style.display = 'block';
      } else {
        pileInfoDiv.style.display = 'none';
      }
      
      // 保存當前訂單資料供付款使用
      window.currentUnpaidBill = {
        charging_bill_id: displayBillId,
        final_amount: bill.final_amount,
        bill_data: bill
      };
      
      // 顯示模態框
      const modal = document.getElementById('unpaidOrderModal');
      if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('charging-modal-open');
      }
    }
    
    // 隱藏未付款訂單模態框
    function hideUnpaidOrderModal() {
      const modal = document.getElementById('unpaidOrderModal');
      if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('charging-modal-open');
      }
    }
    
    // 處理未付款訂單的付款
    async function payUnpaidOrder() {
      if (!window.currentUnpaidBill) {
        alert('無法取得訂單資訊');
        return;
      }
      
      const billId = window.currentUnpaidBill.charging_bill_id;
      const amount = window.currentUnpaidBill.final_amount;
      
      console.log('💳 開始付款未付款訂單，billId:', billId, 'amount:', amount);
      
      // 呼叫後端付款 API 取得藍新金流參數
      try {
        const headers = getAuthHeaders();
        // 後端 API 目前不需 body；若未來支援指定帳單，可於 body 帶入 billId
        const resp = await fetch('http://120.110.115.126:18081/user/purchase/pay', {
          method: 'POST',
          headers
        });
        const result = await resp.json();
        console.log('💳 /user/purchase/pay 回應:', result);

        if (!resp.ok || !result?.success) {
          alert(result?.message || '取得付款資訊失敗，請稍後再試');
          return;
        }

        const data = result.data || {};
        const mid = data.mid;
        const version = data.version;
        const tradeInfo = data.trade_info;
        const tradeSha = data.trade_sha;

        if (!mid || !version || !tradeInfo || !tradeSha) {
          alert('付款參數不完整，請稍後重試');
          return;
        }

        // 動態建立表單送至藍新金流
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'https://ccore.newebpay.com/MPG/mpg_gateway';

        const appendHidden = (name, value) => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          input.value = value;
          form.appendChild(input);
        };

        // 對齊藍新欄位命名
        appendHidden('MerchantID', mid);
        appendHidden('Version', version);
        appendHidden('TradeInfo', tradeInfo);
        appendHidden('TradeSha', tradeSha);

        document.body.appendChild(form);

        // 關閉未付款訂單模態框後送出
        hideUnpaidOrderModal();
        form.submit();
      } catch (err) {
        console.error('❌ 付款流程發生錯誤:', err);
        alert('付款流程發生錯誤，請稍後再試');
      }
    }
    
    // 從 localStorage 讀取 charging_bill_id 並查看未付款訂單
    async function viewUnpaidOrderFromStorage() {
      console.log('🔍 查看未付款訂單（從 localStorage）');
      
      // 從 localStorage 獲取 charging_bill_id
      const chargingBillId = localStorage.getItem('charging_bill_id');
      
      if (!chargingBillId || chargingBillId === 'null' || chargingBillId === '' || chargingBillId === '0') {
        alert('找不到充電帳單 ID，請先完成一次充電');
        return;
      }
      
      console.log('📦 從 localStorage 獲取的 charging_bill_id:', chargingBillId);
      
      // 調用 viewUnpaidBill 函數
      await viewUnpaidBill(chargingBillId, null);
    }
    
    // 將函數暴露到全局作用域
    window.openReservationList = openReservationList;
    window.loadReservationList = loadReservationList;
    window.viewUnpaidBill = viewUnpaidBill;
    window.viewUnpaidOrderFromStorage = viewUnpaidOrderFromStorage;
    window.showUnpaidOrderModal = showUnpaidOrderModal;
    window.hideUnpaidOrderModal = hideUnpaidOrderModal;
    window.payUnpaidOrder = payUnpaidOrder;
    window.checkPaymentStatus = checkPaymentStatus;
    
    // 篩選按鈕事件
    document.getElementById('btn-filter-apply').addEventListener('click', async () => {
      const status = document.getElementById('filter-status').value;
      const startTime = document.getElementById('filter-start-time').value;
      const endTime = document.getElementById('filter-end-time').value;
      const limit = document.getElementById('filter-limit').value;
      
      currentFilters = {
        status: status || undefined,
        start_time: startTime || undefined,
        end_time: endTime || undefined,
        limit: limit
      };
      
      currentPage = 1;
      await loadReservationList(1, currentFilters);
    });
    
    document.getElementById('btn-filter-reset').addEventListener('click', () => {
      document.getElementById('filter-status').value = '';
      document.getElementById('filter-start-time').value = '';
      document.getElementById('filter-end-time').value = '';
      document.getElementById('filter-limit').value = '10';
      
      currentFilters = {};
      currentPage = 1;
      loadReservationList(1, currentFilters);
    });
    
    // 關閉按鈕事件
    document.getElementById('resvlist-close').addEventListener('click', () => {
      document.getElementById('resvlist-backdrop').style.display = 'none';
      document.getElementById('resvlist-modal').style.display = 'none';
    });
    document.getElementById('resvlist-backdrop').addEventListener('click', () => {
      document.getElementById('resvlist-backdrop').style.display = 'none';
      document.getElementById('resvlist-modal').style.display = 'none';
    });
    
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

  <!-- 充電畫面樣式 -->
  <style>
    .charging-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
    }

    .charging-container {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      max-width: 1200px;
      width: 95%;
      max-height: 95vh;
      overflow-y: auto;
    }

    .charging-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #e2e8f0;
    }

    .charging-header h2 {
      margin: 0;
      color: #2d3748;
      font-size: 24px;
      font-weight: 700;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #718096;
      padding: 5px;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .close-btn:hover {
      background: #f7fafc;
      color: #2d3748;
    }

    /* 防止頁面滑動 */
    body.charging-modal-open {
      overflow: hidden;
    }

    .battery-charging-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
      padding: 20px;
      background: linear-gradient(135deg, #f8fafc, #e2e8f0);
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
    }

    .vehicle-background {
      position: relative;
      width: 100%;
      height: 300px;
      border-radius: 12px;
      overflow: hidden;
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .vehicle-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.9;
    }

    .progress-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 20px;
      background: linear-gradient(to bottom, rgba(0,0,0,0.3), transparent, rgba(0,0,0,0.3));
    }

    .progress-bar-overlay {
      position: relative;
      width: 100%;
      height: 8px;
      background: rgba(255, 255, 255, 0.3);
      border-radius: 4px;
      overflow: hidden;
      margin-bottom: 10px;
    }

    .progress-fill-overlay {
      height: 100%;
      background: linear-gradient(90deg, #10b981, #059669);
      border-radius: 4px;
      transition: width 0.5s ease;
      position: relative;
      overflow: hidden;
    }

    .progress-fill-overlay::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      animation: shimmer 2s infinite;
    }

    .progress-percentage-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 10px;
      font-weight: 700;
      color: white;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
    }

    .time-overlay {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .time-info-overlay {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
      background: rgba(0, 0, 0, 0.5);
      padding: 10px 15px;
      border-radius: 8px;
      backdrop-filter: blur(10px);
    }

    .time-label-overlay {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.8);
      font-weight: 500;
    }

    .time-value-overlay {
      font-size: 16px;
      font-weight: 700;
      color: white;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
    }

    @keyframes batteryPulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    @keyframes textGlow {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.2); opacity: 0.7; }
    }

    @keyframes shimmer {
      0% { left: -100%; }
      100% { left: 100%; }
    }

    /* EV 充電樁樣式 - 基於 charger-card */
    .ev-charger {
      position: relative;
      width: 100px;
      height: 200px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .charger-body {
      position: relative;
      width: 80px;
      height: 180px;
      background: linear-gradient(135deg, #1e40af, #2563eb);
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-around;
      padding: 20px 0;
      box-shadow: 0 8px 16px rgba(30, 64, 175, 0.3);
    }

    .charger-status-led {
      width: 12px;
      height: 12px;
      background: #10b981;
      border-radius: 50%;
      animation: ledBlink 2s ease-in-out infinite;
      box-shadow: 0 0 10px rgba(16, 185, 129, 0.8);
    }

    @keyframes ledBlink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
    }

    .charger-display {
      text-align: center;
      color: white;
    }

    .charger-icon {
      font-size: 24px;
      margin-bottom: 8px;
      animation: iconPulse 3s ease-in-out infinite;
    }

    @keyframes iconPulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    .charger-status {
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .charger-cable-outlet {
      width: 20px;
      height: 8px;
      background: #1f2937;
      border-radius: 4px;
      position: relative;
    }

    .charger-cable-outlet::after {
      content: '';
      position: absolute;
      right: -4px;
      top: 50%;
      transform: translateY(-50%);
      width: 6px;
      height: 6px;
      background: #1f2937;
      border-radius: 50%;
    }

    /* 充電線動畫 */
    .charging-cable-animation {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 200px;
      height: 4px;
      z-index: 10;
    }

    .cable-line {
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, #1f2937, #374151, #1f2937);
      border-radius: 2px;
      position: relative;
      overflow: hidden;
    }

    .current-flow {
      position: absolute;
      top: 0;
      left: -20px;
      width: 20px;
      height: 100%;
      background: linear-gradient(90deg, transparent, #fbbf24, transparent);
      animation: currentFlow 2s linear infinite;
    }

    .current-flow:nth-child(2) { animation-delay: 0.7s; }
    .current-flow:nth-child(3) { animation-delay: 1.4s; }

    @keyframes currentFlow {
      0% { left: -20px; }
      100% { left: 100%; }
    }

    /* EV 車輛樣式 */
    .ev-vehicle {
      position: relative;
      width: 160px;
      height: 100px;
    }

    .vehicle-body {
      width: 140px;
      height: 70px;
      background: linear-gradient(135deg, #ffffff, #f1f5f9);
      border-radius: 12px 12px 6px 6px;
      margin: 0 auto;
      position: relative;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .vehicle-windows {
      position: absolute;
      top: 8px;
      left: 8px;
      right: 8px;
      height: 25px;
      background: linear-gradient(135deg, #1e40af, #2563eb);
      border-radius: 6px;
    }

    .vehicle-lights {
      position: absolute;
      bottom: 5px;
      left: 10px;
      right: 10px;
      height: 6px;
      background: linear-gradient(90deg, #ef4444, #dc2626);
      border-radius: 3px;
    }

    .charging-port {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      width: 12px;
      height: 12px;
      background: #1f2937;
      border-radius: 2px;
    }

    .port-glow {
      position: absolute;
      top: -2px;
      left: -2px;
      right: -2px;
      bottom: -2px;
      background: radial-gradient(circle, rgba(251, 191, 36, 0.6), transparent);
      border-radius: 4px;
      animation: portGlow 1.5s ease-in-out infinite;
    }

    @keyframes portGlow {
      0%, 100% { opacity: 0.5; }
      50% { opacity: 1; }
    }

    .vehicle-wheels {
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 140px;
      height: 20px;
    }

    .wheel {
      width: 20px;
      height: 20px;
      background: linear-gradient(135deg, #1f2937, #374151);
      border-radius: 50%;
      position: absolute;
      top: 0;
      border: 2px solid #ffffff;
    }

    .wheel:first-child { left: 15px; }
    .wheel:last-child { right: 15px; }

    /* 電池充電進度 */
    .battery-progress {
      position: absolute;
      top: 20px;
      right: 20px;
      z-index: 20;
    }

    .battery-container {
      width: 60px;
      height: 30px;
    }

    .battery-outline {
      width: 100%;
      height: 100%;
      border: 2px solid #1f2937;
      border-radius: 4px;
      position: relative;
      background: rgba(255, 255, 255, 0.9);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .battery-outline::after {
      content: '';
      position: absolute;
      right: -4px;
      top: 50%;
      transform: translateY(-50%);
      width: 3px;
      height: 12px;
      background: #1f2937;
      border-radius: 0 2px 2px 0;
    }

    .battery-fill {
      position: absolute;
      left: 2px;
      top: 2px;
      bottom: 2px;
      width: 0%;
      background: linear-gradient(90deg, #10b981, #059669);
      border-radius: 2px;
      transition: width 0.5s ease;
    }

    .battery-percentage {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 10px;
      font-weight: 700;
      color: #1f2937;
      z-index: 1;
    }

    /* 充電狀態指示器 */
    .charging-indicators {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 20px;
      z-index: 20;
    }

    .indicator {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .indicator-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      animation: indicatorBlink 2s ease-in-out infinite;
    }

    .charging-indicator .indicator-dot {
      background: #10b981;
    }

    .time-indicator .indicator-dot {
      background: #3b82f6;
    }

    @keyframes indicatorBlink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
    }

    .indicator span {
      font-size: 12px;
      font-weight: 600;
      color: #1f2937;
    }

    /* 資訊卡片樣式 */
    .charging-info {
      margin-bottom: 32px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
    }

    .info-card {
      background: #f8fafc;
      border-radius: 12px;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 16px;
      border: 1px solid #e2e8f0;
      transition: all 0.2s ease;
    }

    .info-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .info-icon {
      font-size: 24px;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .info-content {
      flex: 1;
    }

    .info-label {
      font-size: 12px;
      color: #718096;
      font-weight: 500;
      margin-bottom: 4px;
    }

    .info-value {
      font-size: 16px;
      color: #2d3748;
      font-weight: 700;
    }

    /* 控制按鈕 */
    .charging-controls {
      display: flex;
      justify-content: center;
      margin-top: 24px;
      padding: 0;
      width: 100%;
    }

    .end-charging-btn {
      background: linear-gradient(135deg, #e53e3e, #c53030);
      color: white;
      border: none;
      padding: 20px 32px;
      border-radius: 16px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      width: 100%;
      min-height: 60px;
      white-space: nowrap;
      margin: 0 16px;
    }

    .end-charging-btn:hover {
      background: linear-gradient(135deg, #dc2626, #b91c1c);
      box-shadow: 0 6px 16px rgba(229, 62, 62, 0.4);
      transform: translateY(-2px);
    }

    .btn-icon {
      font-size: 20px;
      flex-shrink: 0;
    }

    .highlight-card {
      background: linear-gradient(135deg, #fef3c7, #fde68a);
      border: 2px solid #f59e0b;
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
    }

    .highlight-card .info-label {
      color: #92400e;
      font-weight: 700;
    }

    .highlight-card .info-value {
      color: #b45309;
      font-weight: 700;
      font-size: 18px;
    }

    /* 響應式設計 */
    @media (max-width: 768px) {
      .charging-container {
        width: 95%;
        margin: 10px;
        padding: 20px;
      }
      
      .charging-scene {
        padding: 20px;
      }
      
      .vehicle-image {
        max-width: 100%;
        width: 100%;
      }
      
      .ev-charger {
        width: 60px;
        height: 120px;
      }
      
      .charger-body {
        width: 50px;
        height: 100px;
      }
      
      .ev-vehicle {
        width: 120px;
        height: 80px;
      }
      
      .vehicle-body {
        width: 100px;
        height: 50px;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
      }
    }

    /* 付款模態框樣式 */
    .payment-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 10000;
      backdrop-filter: blur(5px);
    }

    .payment-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      width: 90%;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
      animation: slideInUp 0.3s ease-out;
    }

    .payment-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 24px 32px;
      border-bottom: 1px solid #e5e7eb;
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      color: white;
      border-radius: 20px 20px 0 0;
    }

    .payment-header h2 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
    }

    .payment-content {
      padding: 32px;
    }

    .payment-summary {
      background: #f8fafc;
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 24px;
      border: 1px solid #e2e8f0;
    }

    .payment-summary h3 {
      margin: 0 0 20px 0;
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #e5e7eb;
    }

    .summary-item:last-child {
      border-bottom: none;
    }

    .summary-item.total {
      font-weight: 700;
      font-size: 18px;
      color: #1f2937;
      background: #f0f9ff;
      padding: 16px;
      border-radius: 8px;
      margin-top: 8px;
    }

    .summary-label {
      color: #6b7280;
      font-weight: 500;
    }

    .summary-value {
      color: #1f2937;
      font-weight: 600;
    }

    .payment-methods {
      margin-bottom: 32px;
    }

    .payment-methods h3 {
      margin: 0 0 16px 0;
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
    }

    .payment-options {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .payment-option {
      display: flex;
      align-items: center;
      padding: 16px;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: white;
    }

    .payment-option:hover {
      border-color: #3b82f6;
      background: #f0f9ff;
    }

    .payment-option input[type="radio"] {
      margin-right: 12px;
      transform: scale(1.2);
    }

    .payment-option input[type="radio"]:checked + .payment-icon + .payment-text {
      color: #3b82f6;
    }

    .payment-option:has(input[type="radio"]:checked) {
      border-color: #3b82f6;
      background: #f0f9ff;
    }

    .payment-icon {
      font-size: 24px;
      margin-right: 12px;
    }

    .payment-text {
      font-size: 16px;
      font-weight: 500;
      color: #1f2937;
    }

    .payment-controls {
      display: flex;
      justify-content: center;
    }

    .confirm-payment-btn {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      border: none;
      padding: 20px 40px;
      border-radius: 16px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      width: 100%;
      min-height: 60px;
    }

    .confirm-payment-btn:hover {
      background: linear-gradient(135deg, #059669, #047857);
      box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
      transform: translateY(-2px);
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 768px) {
      .payment-container {
        width: 95%;
        margin: 20px;
      }
      
      .payment-content {
        padding: 24px;
      }
      
      .payment-header {
        padding: 20px 24px;
      }
      
      .payment-header h2 {
        font-size: 20px;
      }
    }

    /* 新的充電畫面樣式 */
    .charging-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.9);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .charging-container {
      background: white;
      border-radius: 20px;
      padding: 0;
      max-width: 1200px;
      width: 95%;
      max-height: 90vh;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
      position: relative;
    }

    .close-charging-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(0, 0, 0, 0.1);
      border: none;
      color: #333;
      font-size: 24px;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      z-index: 10;
      transition: all 0.3s ease;
    }

    .close-charging-btn:hover {
      background: rgba(0, 0, 0, 0.2);
      transform: scale(1.1);
    }

    .charging-scene {
      padding: 40px 20px 20px;
      text-align: center;
      position: relative;
      background: white;
    }

    .vehicle-container {
      position: relative;
      display: inline-block;
    }

    .vehicle-image {
      max-width: 600px;
      width: 100%;
      height: auto;
      border-radius: 15px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    .progress-overlay {
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80%;
      background: rgba(0, 0, 0, 0.05);
      border-radius: 10px;
      padding: 8px;
    }

    .progress-bar {
      width: 100%;
      height: 12px;
      background: rgba(0, 0, 0, 0.1);
      border-radius: 6px;
      overflow: hidden;
      position: relative;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #10b981, #059669);
      border-radius: 6px;
      transition: width 0.5s ease;
      width: 0%;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 10px;
      font-weight: 600;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }

    .charging-info {
      padding: 20px;
      background: rgba(0, 0, 0, 0.05);
      margin: 0 20px;
      border-radius: 15px;
    }

    .time-display {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .time-item {
      text-align: center;
      flex: 1;
    }

    .time-label {
      display: block;
      color: rgba(0, 0, 0, 0.7);
      font-size: 12px;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .time-value {
      display: block;
      color: #2d3748;
      font-size: 18px;
      font-weight: 700;
      font-family: 'Courier New', monospace;
    }

    .session-info {
      text-align: center;
      padding: 15px;
      background: rgba(0, 0, 0, 0.05);
      border-radius: 10px;
    }

    .session-label {
      display: block;
      color: rgba(0, 0, 0, 0.7);
      font-size: 12px;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .session-value {
      display: block;
      color: #10b981;
      font-size: 16px;
      font-weight: 700;
      font-family: 'Courier New', monospace;
    }

    .charging-actions {
      padding: 20px;
      text-align: center;
    }

    .end-charging-btn {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      border: none;
      padding: 15px 40px;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
      width: 100%;
    }

    .end-charging-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(239, 68, 68, 0.4);
    }
  </style>

  <!-- 充電中畫面 -->
  <div id="chargingModal" class="charging-modal" style="display: none;">
    <div class="charging-container">
      <!-- 右上角離開按鈕 -->
      <button id="closeChargingBtn" class="close-charging-btn">&times;</button>
      
      <!-- 充電車圖片和進度條 -->
        <div class="charging-scene">
        <div class="vehicle-container">
              <img src="{{ asset('images/chargingcar.png') }}" alt="充電中" class="vehicle-image">
              
          <!-- 進度條覆蓋在圖片上 -->
              <div class="progress-overlay">
            <div class="progress-bar">
              <div class="progress-fill" id="progressFill">0%</div>
                </div>
            </div>
          </div>
        </div>
        
      <!-- 時間和資訊顯示 -->
        <div class="charging-info">
        <div class="time-display">
          <div class="time-item">
            <span class="time-label">開始時間</span>
            <span id="currentTime" class="time-value">00:00:00</span>
              </div>
          <div class="time-item">
            <span class="time-label">預計結束時間</span>
            <span id="endTime" class="time-value">00:00:00</span>
              </div>
          <div class="time-item">
            <span class="time-label">充電時長</span>
            <span id="chargingDuration" class="time-value">00:00:00</span>
              </div>
            </div>
            
        <div class="session-info">
          <span class="session-label">會話ID</span>
          <span id="sessionId" class="session-value">-</span>
          </div>
        <div class="session-info">
          <span class="session-label">帳單ID</span>
          <span id="chargingBillId" class="session-value">-</span>
          </div>
        </div>
        
      <!-- 底部結束充電按鈕 -->
      <div class="charging-actions">
        <button id="endChargingBtn" class="end-charging-btn">結束充電</button>
      </div>
    </div>
  </div>

  <!-- 付款頁面 -->
  <div id="paymentModal" class="payment-modal" style="display: none;">
    <div class="payment-container">
      <div class="payment-header">
        <h2>💳 付款</h2>
        <button id="closePaymentBtn" class="close-btn">&times;</button>
      </div>
      
      <div class="payment-content">
        <!-- 充電摘要 -->
        <div class="payment-summary">
          <h3>充電摘要</h3>
          <div class="summary-item">
            <span class="summary-label">充電時間：</span>
            <span id="paymentChargingTime" class="summary-value">00:00:00</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">每小時費率：</span>
            <span id="paymentHourlyRate" class="summary-value">$100/小時</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">服務費：</span>
            <span id="paymentServiceFee" class="summary-value">$0</span>
          </div>
          <div class="summary-item total">
            <span class="summary-label">總金額：</span>
            <span id="paymentTotalAmount" class="summary-value">$0.00</span>
          </div>
        </div>
        
        <!-- 付款方式 -->
        <div class="payment-methods">
          <h3>選擇付款方式</h3>
          <div class="payment-options">
            <label class="payment-option">
              <input type="radio" name="paymentMethod" value="credit" checked>
              <span class="payment-icon">💳</span>
              <span class="payment-text">信用卡</span>
            </label>
            <label class="payment-option">
              <input type="radio" name="paymentMethod" value="mobile">
              <span class="payment-icon">📱</span>
              <span class="payment-text">行動支付</span>
            </label>
            <label class="payment-option">
              <input type="radio" name="paymentMethod" value="cash">
              <span class="payment-icon">💵</span>
              <span class="payment-text">現金</span>
            </label>
          </div>
        </div>
        
        <!-- 付款按鈕 -->
        <div class="payment-controls">
          <button id="confirmPaymentBtn" class="confirm-payment-btn">
            <span class="btn-icon">💳</span>
            確認付款
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- 未付款訂單模態框 -->
  <div id="unpaidOrderModal" class="payment-modal" style="display: none;">
    <div class="payment-container" style="max-width: 600px;">
      <div class="payment-header">
        <h2>📋 未付款訂單</h2>
        <button id="closeUnpaidOrderBtn" class="close-btn">&times;</button>
      </div>
      
      <div class="payment-content">
        <!-- 訂單資訊 -->
        <div class="payment-summary">
          <h3>訂單詳情</h3>
          <div class="summary-item">
            <span class="summary-label">充電帳單 ID：</span>
            <span id="unpaidBillId" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">會話 ID：</span>
            <span id="unpaidSessionId" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">開始時間：</span>
            <span id="unpaidStartTime" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">結束時間：</span>
            <span id="unpaidEndTime" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">充電時長：</span>
            <span id="unpaidDuration" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">每小時價格：</span>
            <span id="unpaidPricePerHour" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">服務費：</span>
            <span id="unpaidServiceFee" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">總金額：</span>
            <span id="unpaidTotalAmount" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">折扣金額：</span>
            <span id="unpaidDiscountAmount" class="summary-value">-</span>
          </div>
          <div class="summary-item total">
            <span class="summary-label">最終金額：</span>
            <span id="unpaidFinalAmount" class="summary-value">-</span>
          </div>
        </div>
        
        <!-- 充電樁資訊 -->
        <div id="unpaidPileInfo" class="payment-summary" style="margin-top: 20px; display: none;">
          <h3>充電樁資訊</h3>
          <div class="summary-item">
            <span class="summary-label">型號：</span>
            <span id="unpaidPileModel" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">連接器類型：</span>
            <span id="unpaidPileConnector" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">最大功率：</span>
            <span id="unpaidPileMaxKw" class="summary-value">-</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">地址：</span>
            <span id="unpaidPileAddress" class="summary-value">-</span>
          </div>
        </div>
        
        <!-- 付款按鈕 -->
        <div class="payment-controls" style="margin-top: 24px;">
          <button id="payUnpaidOrderBtn" class="confirm-payment-btn" style="background: linear-gradient(135deg, #dc3545, #c82333);">
            <span class="btn-icon">💳</span>
            付款
          </button>
        </div>
      </div>
    </div>
  </div>

</body>
</html>