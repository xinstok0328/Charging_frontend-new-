{{-- resources/views/auth/register.blade.php --}}
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        建立新帳號。標有「※」為必填。
    </div>

    <form id="registerForm" class="space-y-4">
        @csrf

        {{-- 你的自訂欄位（目前僅前端顯示，後端尚未寫入） --}}
        <div>
            <x-input-label for="account" value="帳號（建議用 Email）" />
            <x-text-input id="account" name="account" type="text"
                class="block mt-1 w-full" :value="old('account')" autocomplete="username"/>
            <p class="text-xs text-gray-500 mt-1">目前後端未使用此欄位，若要作為登入帳號可再調整。</p>
        </div>

        <div>
            <x-input-label for="name" value="※ 姓名" />
            <x-text-input id="name" name="name" type="text"
                class="block mt-1 w-full" :value="old('name')" required autofocus autocomplete="name"/>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" value="手機號碼" />
            <x-text-input id="phone" name="phone" type="tel"
                class="block mt-1 w-full" :value="old('phone')" placeholder="例如：0900123456" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="※ Email" />
            <x-text-input id="email" name="email" type="email"
                class="block mt-1 w-full" :value="old('email')" required autocomplete="username"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <div id="email-status" class="mt-2 text-sm"></div>
        </div>

       {{-- 驗證碼欄位區 --}}
        <div class="mt-3 flex items-center gap-2">
            <input
                id="verifyCode"
                type="text"
                name="verifyCode"
                class="border rounded p-2 w-full"
                placeholder="輸入收到的驗證碼"
        >
        <button
            id="getCodeBtn"
            type="button"
            class="px-3 py-2 bg-blue-500 text-white rounded"
        >
            取得驗證碼
        </button>
        <button
            id="checkCodeBtn"
            type="button"
            class="px-3 py-2 bg-gray-500 text-white rounded"
        >
            確認驗證碼
        </button>
    </div>

    {{-- 驗證結果訊息 --}}
    <div id="msg" class="mt-2 text-sm"></div>

    {{-- 驗證通過旗標（隱藏） --}}
    <input type="hidden" id="emailCodeOK" name="emailCodeOK" value="0">



        {{-- Breeze 預設會處理的欄位 --}}
        <div class="pt-2">
            <x-input-label for="password" value="※ 密碼" />
            <x-text-input id="password" name="password" type="password"
                class="block mt-1 w-full" required autocomplete="new-password"/>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="※ 確認密碼" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                class="block mt-1 w-full" required autocomplete="new-password"/>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                已有帳號？前往登入
            </a>
            <x-primary-button>建立帳號</x-primary-button>
        </div>
    </form>

    <script>
        // Email 格式驗證（不檢查是否已註冊）
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            const statusDiv = document.getElementById('email-status');
            
            if (email) {
                // 簡單的 email 格式驗證
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailRegex.test(email)) {
                    statusDiv.innerHTML = '<span class="text-green-500">Email 格式正確</span>';
                        } else {
                    statusDiv.innerHTML = '<span class="text-red-500">Email 格式不正確</span>';
                        }
            } else {
                statusDiv.innerHTML = '';
            }
        });

        // 註冊表單提交

    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        console.log('表單提交事件被觸發！');
        e.preventDefault();

        const account = document.getElementById('account').value.trim();
        const name = document.getElementById('name').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const password_confirmation = document.getElementById('password_confirmation').value.trim();
        const emailCodeOK = document.getElementById('emailCodeOK').value;

        const msgDiv = document.getElementById('msg');
        
        // 檢查驗證碼是否已通過
        if (emailCodeOK !== '1') {
            msgDiv.innerHTML = '<span class="text-red-500">請先完成 email 驗證碼驗證</span>';
            return;
        }
        
        // 檢查密碼確認
        if (password !== password_confirmation) {
            msgDiv.innerHTML = '<span class="text-red-500">密碼與確認密碼不一致</span>';
            return;
        }
        
        msgDiv.innerHTML = '<span class="text-blue-500">註冊中...</span>';

        try {
            // 直接調用外部 API
            console.log('=== 註冊 API 調用開始 ===');
            console.log('註冊數據:', {
                account: email,
                password: password,
                name: name,
                email: email,
                phone: phone,
                file_id: 0
            });
            
            const res = await fetch('http://120.110.115.126:18081/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': '*/*'
                },
                body: JSON.stringify({
                    account: email,  // 使用 email 作為 account
                    password: password,
                    name: name,
                    email: email,
                    phone: phone,
                    file_id: 0
                })
            });

            console.log('註冊 API 響應狀態:', res.status);
            console.log('註冊 API 響應 URL:', res.url);

            const data = await res.json().catch(() => ({}));
            console.log('註冊 API 響應數據:', data);

            if (res.ok && (data.code === 20000 || data.success === true)) {
                msgDiv.innerHTML = '<span class="text-green-600">註冊成功！正在跳轉到登入頁面...</span>';
                setTimeout(() => window.location.href = '/login', 1500);
            } else {
                // 根據指導原則：精準顯示錯誤訊息
                console.log('註冊失敗，完整回應:', data);
                
                // 檢查欄位特定錯誤
                if (data.errors) {
                    if (data.errors.email) {
                        msgDiv.innerHTML = `<span class="text-red-500">Email 錯誤：${data.errors.email}</span>`;
                    } else if (data.errors.phone) {
                        msgDiv.innerHTML = `<span class="text-red-500">手機號碼錯誤：${data.errors.phone}</span>`;
                    } else if (data.errors.account) {
                        msgDiv.innerHTML = `<span class="text-red-500">帳號錯誤：${data.errors.account}</span>`;
                    } else {
                        msgDiv.innerHTML = `<span class="text-red-500">欄位錯誤：${JSON.stringify(data.errors)}</span>`;
                    }
                    return;
                }
                
                // 檢查業務邏輯錯誤（根據 code 和 field）
                if (data.code === 401) {
                    // 檢查是否為 email 已註冊的錯誤
                    if (data.field === 'email' || (data.message && data.message.includes('已經註冊'))) {
                        msgDiv.innerHTML = `
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-blue-800 font-medium">此電子郵件已註冊</span>
                                </div>
                                <div class="text-blue-700 text-sm">
                                    <p class="mb-2">請選擇以下其中一種方式：</p>
                                    <div class="space-y-1">
                                        <p>• <a href="/login" class="underline hover:text-blue-900">使用此 email 直接登入</a></p>
                                        <p>• 或使用不同的 email 重新註冊</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <a href="/login" class="bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600 transition-colors">
                                        前往登入
                                    </a>
                                    <button onclick="clearForm()" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 transition-colors">
                                        重新註冊
                                    </button>
                                </div>
                            </div>
                        `;
                    } else if (data.field === 'phone') {
                        msgDiv.innerHTML = `<span class="text-red-500">此手機號碼已註冊</span>`;
                    } else if (data.field === 'account') {
                        msgDiv.innerHTML = `<span class="text-red-500">此帳號已註冊</span>`;
                    } else {
                        msgDiv.innerHTML = `<span class="text-red-500">註冊失敗：${data.message || '未知錯誤'}</span>`;
                    }
                } else {
                    // 其他錯誤
                msgDiv.innerHTML = `<span class="text-red-500">註冊失敗：${data.message || '請確認欄位或稍後再試'}</span>`;
                }
            }
        } catch (err) {
            console.error('註冊 API 錯誤:', err);
            msgDiv.innerHTML = `<span class="text-red-500">伺服器錯誤：${err.message}</span>`;
        }
        
        console.log('=== 註冊 API 調用結束 ===');
    });


        // 取得驗證碼功能
        document.getElementById('getCodeBtn').addEventListener('click', async function() {
            const email = document.getElementById('email').value.trim();
            const msgDiv = document.getElementById('msg');
            const getCodeBtn = document.getElementById('getCodeBtn');
            
            // Email 格式檢查
            const isEmail = (s) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);
            
            if (!email) {
                msgDiv.innerHTML = '<span class="text-red-500">請先輸入 Email</span>';
                return;
            }
            
            if (!isEmail(email)) {
                msgDiv.innerHTML = '<span class="text-red-500">Email 格式不正確</span>';
                return;
            }
            
            try {
                msgDiv.innerHTML = '<span class="text-blue-500">正在發送驗證碼…</span>';
                getCodeBtn.disabled = true;
                getCodeBtn.textContent = '發送中…';
                
                const url = `http://120.110.115.126:18081/auth/send_mail_code?loginMail=${encodeURIComponent(email)}`;
                const res = await fetch(url, { method: 'GET', headers: { Accept: '*/*' } });
                const data = await res.json().catch(() => ({}));
                
                if (res.ok && (data?.success === true || data?.code === 20000)) {
                    msgDiv.innerHTML = `<span class="text-green-500">${data?.message || '驗證碼已寄出，請到信箱查收！'}</span>`;
                    
                    // 開始倒數計時
                    let left = 60;
                    const orig = getCodeBtn.textContent;
                    const timer = setInterval(() => {
                        getCodeBtn.textContent = `${left}s 後可再發送`;
                        left--;
                        if (left < 0) {
                            clearInterval(timer);
                            getCodeBtn.disabled = false;
                            getCodeBtn.textContent = '取得驗證碼';
                        }
                    }, 1000);
                } else {
                    msgDiv.innerHTML = `<span class="text-red-500">${data?.message || `發送失敗（HTTP ${res.status}）`}</span>`;
                    getCodeBtn.disabled = false;
                    getCodeBtn.textContent = '取得驗證碼';
                }
            } catch (err) {
                msgDiv.innerHTML = `<span class="text-red-500">發送失敗：${err?.message || err}</span>`;
                getCodeBtn.disabled = false;
                getCodeBtn.textContent = '取得驗證碼';
            }
        });

        // 驗證碼確認功能
        document.getElementById('checkCodeBtn').addEventListener('click', async function() {
            const email = document.getElementById('email').value.trim();
            const verifyCode = document.getElementById('verifyCode').value.trim();
            const msgDiv = document.getElementById('msg');
            const emailCodeOKInput = document.getElementById('emailCodeOK');
            
            if (!email || !verifyCode) {
                msgDiv.innerHTML = '<span class="text-red-500">請輸入 email 和驗證碼</span>';
                return;
            }
            
            msgDiv.innerHTML = '<span class="text-blue-500">驗證中...</span>';
            
            try {
                // 先測試 API 是否可達
                console.log('=== 驗證碼 API 調試開始 ===');
                console.log('Email:', email);
                console.log('驗證碼:', verifyCode);
                
                // 先測試 API 端點是否可達
                console.log('測試 API 端點可達性...');
                try {
                    const testRes = await fetch('http://120.110.115.126:18081/auth/check_mail_code', {
                        method: 'OPTIONS',
                        headers: { Accept: '*/*' }
                    });
                    console.log('OPTIONS 測試結果:', testRes.status, testRes.headers);
                } catch (e) {
                    console.log('OPTIONS 測試失敗:', e);
                }
                
                // 根據 Swagger 文檔，使用 POST + Query 參數格式
                const testMethods = [
                    {
                        name: 'POST + Query (Swagger 格式)',
                        url: `http://120.110.115.126:18081/auth/check_mail_code?loginMail=${encodeURIComponent(email)}&verifyCode=${encodeURIComponent(verifyCode)}`,
                        options: {
                            method: 'POST',
                            headers: { 
                                'Accept': '*/*'
                            }
                        }
                    },
                    {
                        name: 'POST + Query (code 參數)',
                        url: `http://120.110.115.126:18081/auth/check_mail_code?loginMail=${encodeURIComponent(email)}&code=${encodeURIComponent(verifyCode)}`,
                        options: {
                            method: 'POST',
                            headers: { 
                                'Accept': '*/*'
                            }
                        }
                    },
                    {
                        name: 'POST + JSON Body (備用)',
                        url: 'http://120.110.115.126:18081/auth/check_mail_code',
                        options: {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'Accept': '*/*'
                            },
                            body: JSON.stringify({
                                loginMail: email,
                                verifyCode: verifyCode
                            })
                        }
                    }
                ];
                
                let success = false;
                let lastError = null;
                
                for (const method of testMethods) {
                    try {
                        console.log(`嘗試 ${method.name}:`, method.url);
                        const res = await fetch(method.url, method.options);
                        console.log(`${method.name} 響應狀態:`, res.status);
                        
                        const data = await res.json().catch(() => ({}));
                        console.log(`${method.name} 響應數據:`, data);
                        console.log(`${method.name} 請求體:`, method.options.body);
                        
                        if (res.ok && (data?.success === true || data?.code === 20000)) {
                            console.log(`${method.name} 成功！`);
                            msgDiv.innerHTML = '<span class="text-green-500">✅ 驗證成功，Email 已通過驗證！</span>';
                            emailCodeOKInput.value = '1';
                            
                            // 鎖定欄位避免再改
                            document.getElementById('email').readOnly = true;
                            document.getElementById('verifyCode').readOnly = true;
                            document.getElementById('checkCodeBtn').disabled = true;
                            success = true;
                            break;
                    } else {
                            console.log(`${method.name} 失敗:`, res.status, data);
                            lastError = data?.message || `HTTP ${res.status}`;
                        }
                    } catch (err) {
                        console.log(`${method.name} 錯誤:`, err);
                        lastError = err.message;
                    }
                }
                
                if (!success) {
                    console.log('所有方法都失敗了，最後錯誤:', lastError);
                    msgDiv.innerHTML = `<span class="text-red-500">❌ 驗證碼驗證失敗：${lastError}</span>`;
                    emailCodeOKInput.value = '0';
                }
                
        console.log('=== 驗證碼 API 調試結束 ===');
    } catch (err) {
        console.error('驗證碼 API 總體錯誤:', err);
        msgDiv.innerHTML = `<span class="text-red-500">驗證失敗：${err?.message || err}</span>`;
                    emailCodeOKInput.value = '0';
    }
});

// 清除表單函數
function clearForm() {
    // 清除所有輸入欄位
    document.getElementById('name').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('email').value = '';
    document.getElementById('verifyCode').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').value = '';
    document.getElementById('emailCodeOK').value = '0';
    
    // 清除狀態訊息
    document.getElementById('msg').innerHTML = '';
    document.getElementById('email-status').innerHTML = '';
    
    // 恢復表單狀態
    document.getElementById('registerForm').style.pointerEvents = 'auto';
    document.getElementById('registerForm').style.opacity = '1';
    
    // 恢復按鈕狀態
    const getCodeBtn = document.getElementById('getCodeBtn');
    const checkCodeBtn = document.getElementById('checkCodeBtn');
    if (getCodeBtn) {
        getCodeBtn.disabled = false;
        getCodeBtn.textContent = '取得驗證碼';
    }
    if (checkCodeBtn) {
        checkCodeBtn.disabled = false;
        checkCodeBtn.textContent = '確認驗證碼';
    }
    
    // 恢復輸入欄位狀態
    document.getElementById('email').readOnly = false;
    document.getElementById('verifyCode').readOnly = false;
    
    console.log('表單已清除，可以重新註冊');
}
    </script>
</x-guest-layout>
