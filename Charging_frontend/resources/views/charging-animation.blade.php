<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>充電中</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .charging-container {
            text-align: center;
            color: white;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .pile-info {
            font-size: 18px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .lightning {
            font-size: 80px;
            margin-bottom: 20px;
            animation: flash 1.5s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.5));
        }

        @keyframes flash {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.1); }
        }

        .charging-text {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 30px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .battery {
            width: 280px;
            height: 140px;
            border: 5px solid white;
            border-radius: 15px;
            position: relative;
            margin: 0 auto 40px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .battery::before {
            content: '';
            position: absolute;
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 60px;
            background: white;
            border-radius: 0 8px 8px 0;
        }

        .battery-level {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #10b981, #059669);
            animation: charging 2s ease-in-out infinite;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes charging {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .battery-percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 42px;
            font-weight: bold;
            z-index: 1;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .status-text {
            font-size: 20px;
            opacity: 0.95;
            margin-top: 20px;
            min-height: 30px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 0.95; transform: translateY(0); }
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: float 3s infinite;
            opacity: 0.6;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.6; }
            50% { transform: translateY(-30px) scale(1.2); opacity: 0.9; }
        }

        .info-box {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 16px;
        }

        /* 讓結束充電按鈕固定在底部且不被動畫遮住 */
        .footer-actions {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 16px;
            display: flex;
            justify-content: center;
            padding: 0 16px;
            z-index: 1000;
            pointer-events: none; /* 讓容器不攔截點擊，只讓按鈕可點 */
        }
        .end-charge-btn {
            pointer-events: auto;
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 9999px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            cursor: pointer;
            transition: transform .1s ease, opacity .2s ease;
        }
        .end-charge-btn:active { transform: translateY(1px); }
        @media (max-width: 420px) {
            .end-charge-btn { width: 100%; }
        }
    </style>
</head>
<body>
    <!-- 背景粒子效果 -->
    <div class="particles" id="particles"></div>

    <div class="charging-container">
        <div class="pile-info">
            充電樁 #<span id="pileNumber">-</span>
        </div>

        <div class="lightning">⚡</div>
        <h1 class="charging-text">充電進行中</h1>
        
        <div class="battery">
            <div class="battery-level" id="batteryLevel"></div>
            <div class="battery-percentage" id="percentage">0%</div>
        </div>
        
        <p class="status-text" id="statusText">正在啟動充電系統...</p>

        <div class="info-box">
            <div class="info-row">
                <span>開始時間:</span>
                <span id="infoStartTime">-</span>
            </div>
            <div class="info-row">
                <span>結束時間:</span>
                <span id="infoEndTime">-</span>
            </div>
            <div class="info-row">
                <span>預計時長:</span>
                <span id="infoDuration">-</span>
            </div>
        </div>
    </div>
    
    <!-- 固定在底部的結束充電按鈕 -->
    <div class="footer-actions">
        <button id="endChargeNow" class="end-charge-btn">結束充電</button>
    </div>

    <script>
        // 全域變數
        let chargingStarted = false;
        let progress = 0;
        let chargingInterval = null;

        // 創建背景粒子
        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.width = Math.random() * 5 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 3 + 's';
                particle.style.animationDuration = Math.random() * 2 + 2 + 's';
                container.appendChild(particle);
            }
        }

        // 載入預約資訊
        function loadReservationInfo() {
            const data = localStorage.getItem('activeReservation');
            if (data) {
                const reservation = JSON.parse(data);
                
                // 顯示充電樁編號
                document.getElementById('pileNumber').textContent = reservation.pile_id;
                
                // 格式化並顯示時間
                const startDate = new Date(reservation.start_time);
                const endDate = new Date(reservation.end_time);
                
                document.getElementById('infoStartTime').textContent = 
                    formatTime(startDate);
                document.getElementById('infoEndTime').textContent = 
                    formatTime(endDate);
                
                // 計算時長
                const duration = Math.round((endDate - startDate) / 1000 / 60);
                document.getElementById('infoDuration').textContent = 
                    duration + ' 分鐘';
            }
        }

        function formatTime(date) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        }

        // 呼叫後端啟動充電（代理至 /user/purchase/start）
        async function startChargingSession() {
            const statusText = document.getElementById('statusText');
            console.log('開始呼叫後端 API...');
            
            try {
                statusText.textContent = '正在向後端請求啟動充電...';

                // 從 localStorage 取得預約資訊
                const reservationData = localStorage.getItem('activeReservation');
                if (!reservationData) {
                    statusText.textContent = '找不到預約資訊，請重新預約';
                    console.error('找不到預約資訊');
                    return false;
                }

                const reservation = JSON.parse(reservationData);
                console.log('預約資訊:', reservation);

                // 取得 CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                
                if (csrfToken) {
                //     headers['X-CSRF-TOKEN'] = csrfToken;
                // }

                // // 準備請求資料
                // const requestData = {
                //     reservation: {
                //         id: reservation.id,
                //         reservation_id: reservation.id, // 確保有 reservation_id
                //         pile_id: reservation.pile_id,
                //         start_time: reservation.start_time,
                //         end_time: reservation.end_time
                //     }
                // };

                // console.log('發送請求到 /user/purchase/start', requestData);
                // const resp = await fetch('/user/purchase/start', {
                    method: 'POST',
                    headers,
                    credentials: 'same-origin',
                    body: '' // 空 body，依賴 token 識別用戶
                });

                console.log('API 回應狀態:', resp.status);

                if (resp.status === 401) {
                    statusText.textContent = '未授權，請重新登入';
                    console.error('401 未授權');
                    setTimeout(() => { window.location.href = '/login'; }, 1200);
                    return false;
                }

                if (!resp.ok) {
                    const text = await resp.text();
                    statusText.textContent = '啟動失敗：' + text;
                    console.error('API 錯誤:', resp.status, text);
                    return false;
                }

                const json = await resp.json().catch(() => ({}));
                console.log('API 回應:', json);
                
                if (json && json.success === false) {
                    statusText.textContent = '啟動失敗：' + (json.message || '未知錯誤');
                    console.error('API 回傳失敗:', json);
                    return false;
                }

                // 儲存 session_id 作為充電會話的唯一識別符
                if (json && json.data && json.data.session_id) {
                    localStorage.setItem('chargingSessionId', json.data.session_id);
                    console.log('已儲存充電會話 ID:', json.data.session_id);
                }

                statusText.textContent = '充電已開始，建立動畫...';
                console.log('API 呼叫成功，開始動畫');
                return true;
            } catch (e) {
                statusText.textContent = '連線失敗，請稍後再試';
                console.error('API 呼叫異常:', e);
                return false;
            }
        }

        // 充電動畫邏輯
        function startChargingAnimation() {
            const batteryLevel = document.getElementById('batteryLevel');
            const percentage = document.getElementById('percentage');
            const statusText = document.getElementById('statusText');

            const statusMessages = [
                { at: 0, text: '正在啟動充電系統...' },
                { at: 15, text: '建立連線中...' },
                { at: 30, text: '充電已開始' },
                { at: 50, text: '充電進行順利' },
                { at: 70, text: '電量持續增加中' },
                { at: 85, text: '即將完成...' },
                { at: 100, text: '✓ 充電準備完成!' }
            ];

            chargingInterval = setInterval(() => {
                if (progress < 100) {
                    progress += 1;
                    batteryLevel.style.width = progress + '%';
                    percentage.textContent = progress + '%';

                    // 更新狀態文字
                    const currentStatus = statusMessages.find(s => progress >= s.at);
                    if (currentStatus) {
                        statusText.textContent = currentStatus.text;
                    }
                } else {
                    clearInterval(chargingInterval);
                    
                    // 充電動畫完成，跳轉到實際充電頁面
                    setTimeout(() => {
                        // 清除已完成的預約，但保留充電會話 ID
                        localStorage.removeItem('activeReservation');
                        
                        // 跳轉到實際的充電服務頁面（攜帶 session_id）
                        window.location.href = '/charging-service';
                    }, 1500);
                }
            }, 50); // 總共 5 秒完成
        }

        // 初始化
        document.addEventListener('DOMContentLoaded', async () => {
            createParticles();
            loadReservationInfo();
            
            // 先呼叫 API，成功後再開始動畫
            const apiSuccess = await startChargingSession();
            if (apiSuccess) {
                // 延遲一點再開始動畫，讓用戶看到 API 成功訊息
                setTimeout(() => {
                    startChargingAnimation();
                }, 1000);
            } else {
                // API 失敗，顯示錯誤但還是可以手動開始動畫（測試用）
                console.log('API 失敗，但可以手動測試動畫');
            }
        });

        // 防止返回
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // 結束充電按鈕事件
        document.getElementById('endChargeNow')?.addEventListener('click', async () => {
            try {
                const sessionId = localStorage.getItem('chargingSessionId')
                    || JSON.parse(localStorage.getItem('activeReservation') || '{}').id
                    || 0;
                const resp = await fetch('/user/purchase/end', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ session_id: Number(sessionId) || 0 })
                });
                const json = await resp.json().catch(() => ({}));
                if (!resp.ok || json?.success === false) {
                    alert('結束充電失敗，請稍後重試');
                    return;
                }
                // 成功後導回地圖或付款流程由其他頁面處理
                window.location.href = '/map';
            } catch (e) {
                console.error('結束充電失敗', e);
                alert('結束充電失敗，請稍後重試');
            }
        });
    </script>
</body>
</html>