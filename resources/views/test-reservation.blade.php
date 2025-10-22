<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>測試預約資料</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">測試預約資料</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">建立測試預約</h2>
            <button id="createTestReservationBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                建立測試預約資料
            </button>
            <div id="reservationResult" class="mt-4 p-4 bg-gray-100 rounded hidden">
                <h3 class="font-semibold">預約資料：</h3>
                <pre id="reservationData" class="mt-2 text-sm"></pre>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">跳轉到充電動畫</h2>
            <button id="goToChargingBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                跳轉到充電動畫頁面
            </button>
            <p class="mt-2 text-sm text-gray-600">需要先建立測試預約資料</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">清除預約資料</h2>
            <button id="clearReservationBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                清除預約資料
            </button>
        </div>
    </div>

    <script>
        document.getElementById('createTestReservationBtn').addEventListener('click', () => {
            // 建立測試預約資料
            const testReservation = {
                id: Date.now(),
                pile_id: 1,
                start_time: new Date().toISOString(),
                end_time: new Date(Date.now() + 60 * 60 * 1000).toISOString(), // 1小時後
                status: 'confirmed'
            };

            // 儲存到 localStorage
            localStorage.setItem('activeReservation', JSON.stringify(testReservation));

            // 顯示結果
            const resultDiv = document.getElementById('reservationResult');
            const dataDiv = document.getElementById('reservationData');
            
            resultDiv.classList.remove('hidden');
            dataDiv.textContent = JSON.stringify(testReservation, null, 2);
            
            console.log('測試預約資料已建立:', testReservation);
        });

        document.getElementById('goToChargingBtn').addEventListener('click', () => {
            const reservationData = localStorage.getItem('activeReservation');
            if (!reservationData) {
                alert('請先建立測試預約資料');
                return;
            }
            
            window.location.href = '/charging-animation';
        });

        document.getElementById('clearReservationBtn').addEventListener('click', () => {
            localStorage.removeItem('activeReservation');
            document.getElementById('reservationResult').classList.add('hidden');
            console.log('預約資料已清除');
        });
    </script>
</body>
</html>

