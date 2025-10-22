<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>等待預約時間</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl p-8">
            <!-- 預約資訊卡片 -->
            <div class="text-center mb-8">
                <div class="inline-block p-4 bg-blue-100 rounded-full mb-4">
                    <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">預約已確認</h1>
                <p class="text-gray-600">系統將在預約時間自動為您啟動服務</p>
            </div>

            <!-- 預約詳情 -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-6">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">充電樁編號</span>
                        <span id="pileId" class="text-gray-900 font-bold">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">預約開始時間</span>
                        <span id="startTime" class="text-gray-900 font-bold">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">預約結束時間</span>
                        <span id="endTime" class="text-gray-900 font-bold">-</span>
                    </div>
                </div>
            </div>

            <!-- 倒數計時器 -->
            <div class="text-center">
                <div id="countdownContainer" class="mb-6">
                    <div class="text-sm text-gray-500 mb-2">距離預約時間還有</div>
                    <div id="countdown" class="text-5xl font-bold text-blue-600 mb-2">
                        --:--:--
                    </div>
                    <div id="countdownText" class="text-gray-600">計算中...</div>
                </div>

                <!-- 已到時間提示 -->
                <div id="readyContainer" class="hidden">
                    <div class="text-6xl mb-4">🎉</div>
                    <div class="text-2xl font-bold text-green-600 mb-2">預約時間已到!</div>
                    <div class="text-gray-600 mb-4">正在為您準備...</div>
                    <div class="inline-block">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    </div>
                </div>

                <!-- 取消預約按鈕 -->
                <button id="cancelBtn" class="mt-6 px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    取消預約
                </button>
            </div>
        </div>
    </div>

    <script>
        // ============================
        // 核心功能: 預約時間檢查器
        // ============================
        
        class ReservationChecker {
            constructor() {
                this.checkInterval = null;
                this.reservation = null;
                this.TIMEZONE = 'Asia/Taipei'; // 對應你的設定
            }

            // 初始化
            init() {
                this.loadReservation();
                if (!this.reservation) {
                    this.handleNoReservation();
                    return;
                }

                this.displayReservationInfo();
                this.startChecking();
            }

            // 從 localStorage 載入預約
            loadReservation() {
                const data = localStorage.getItem('activeReservation');
                if (data) {
                    this.reservation = JSON.parse(data);
                }
            }

            // 沒有預約時的處理
            handleNoReservation() {
                alert('找不到預約資訊');
                window.location.href = '/'; // 回到首頁
            }

            // 顯示預約資訊
            displayReservationInfo() {
                document.getElementById('pileId').textContent = this.reservation.pile_id;
                document.getElementById('startTime').textContent = 
                    this.formatDateTime(this.reservation.start_time);
                document.getElementById('endTime').textContent = 
                    this.formatDateTime(this.reservation.end_time);
            }

            // 格式化日期時間
            formatDateTime(dateString) {
                const date = new Date(dateString);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${year}/${month}/${day} ${hours}:${minutes}`;
            }

            // 開始檢查
            startChecking() {
                // 立即執行一次
                this.checkTime();

                // 每秒檢查一次
                this.checkInterval = setInterval(() => {
                    this.checkTime();
                }, 1000);
            }

            // 核心: 檢查時間
            checkTime() {
                const now = new Date();
                const startTime = new Date(this.reservation.start_time);
                
                // 計算時間差（毫秒）
                const timeDiff = startTime - now;

                // 🎯 關鍵判斷: 時間到了
                if (timeDiff <= 0) {
                    this.handleTimeReached();
                    return;
                }

                // 更新倒數計時顯示
                this.updateCountdown(timeDiff);
            }

            // 更新倒數計時顯示
            updateCountdown(milliseconds) {
                const seconds = Math.floor(milliseconds / 1000);
                const minutes = Math.floor(seconds / 60);
                const hours = Math.floor(minutes / 60);
                const days = Math.floor(hours / 24);

                const displayHours = hours % 24;
                const displayMinutes = minutes % 60;
                const displaySeconds = seconds % 60;

                // 格式化顯示
                let countdownText = '';
                if (days > 0) {
                    countdownText = `${days} 天 ${displayHours} 小時`;
                    document.getElementById('countdown').textContent = 
                        `${String(days).padStart(2, '0')}:${String(displayHours).padStart(2, '0')}:${String(displayMinutes).padStart(2, '0')}`;
                } else {
                    document.getElementById('countdown').textContent = 
                        `${String(displayHours).padStart(2, '0')}:${String(displayMinutes).padStart(2, '0')}:${String(displaySeconds).padStart(2, '0')}`;
                    
                    if (hours > 0) {
                        countdownText = `${hours} 小時 ${displayMinutes} 分鐘`;
                    } else if (minutes > 0) {
                        countdownText = `${minutes} 分鐘 ${displaySeconds} 秒`;
                    } else {
                        countdownText = `${seconds} 秒`;
                    }
                }

                document.getElementById('countdownText').textContent = countdownText;
            }

            // 🚀 時間到達時的處理
            handleTimeReached() {
                // 停止檢查
                clearInterval(this.checkInterval);

                // 更新預約狀態
                this.reservation.status = 'started';
                localStorage.setItem('activeReservation', JSON.stringify(this.reservation));

                // 顯示準備中的UI
                document.getElementById('countdownContainer').classList.add('hidden');
                document.getElementById('readyContainer').classList.remove('hidden');

                // 延遲 1 秒後跳轉到充電動畫頁面
                setTimeout(() => {
                    window.location.href = '/charging-animation?id=' + this.reservation.id;
                }, 1500);
            }

            // 取消預約
            cancelReservation() {
                if (confirm('確定要取消預約嗎?')) {
                    // 清除 localStorage
                    localStorage.removeItem('activeReservation');
                    
                    // 如果需要呼叫 API 取消預約，在這裡加入
                    // await fetch('/api/reservations/' + this.reservation.id, { method: 'DELETE' });

                    // 跳轉回首頁
                    window.location.href = '/';
                }
            }
        }

        // ============================
        // 初始化
        // ============================
        
        const checker = new ReservationChecker();
        
        document.addEventListener('DOMContentLoaded', () => {
            checker.init();

            // 取消按鈕
            document.getElementById('cancelBtn').addEventListener('click', () => {
                checker.cancelReservation();
            });
        });

        // 防止用戶按上一頁時清除定時器
        window.addEventListener('beforeunload', () => {
            if (checker.checkInterval) {
                clearInterval(checker.checkInterval);
            }
        });

        // 當頁面重新獲得焦點時，重新檢查時間
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && checker.reservation) {
                checker.checkTime();
            }
        });
    </script>
</body>
</html>