{{-- resources/views/user/profile.blade.php --}}
@extends('layouts.app')

@section('title', '個人資料設定')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 space-y-10" x-data>
    <header class="mb-6">
        <h1 class="text-2xl font-semibold">個人資料設定</h1>
        <p class="text-sm text-gray-500 mt-1">更新基本資料、變更密碼，或刪除帳號。</p>
    </header>

    {{-- 全域訊息區 --}}
    <div id="flash" class="hidden rounded-md p-3 text-sm"></div>

    {{-- 1) 基本資料 --}}
    <section class="space-y-4">
        <h2 class="text-lg font-medium">基本資料</h2>
        <form id="form-profile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">姓名（name）</label>
                    <input name="name" class="w-full rounded-md border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Email（email）</label>
                    <input name="email" type="email" class="w-full rounded-md border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600">手機（phone）</label>
                    <input name="phone" class="w-full rounded-md border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600">檔案ID（file_id）</label>
                    <input name="file_id" type="number" class="w-full rounded-md border px-3 py-2" />
                </div>
            </div>

            {{-- 大頭貼（只顯示 URL；如需上傳可再加） --}}
            <div id="avatarBox" class="mt-2 text-sm text-gray-600"></div>

            <div class="pt-2">
                <button type="submit" class="rounded-md bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                    儲存基本資料
                </button>
            </div>
        </form>
    </section>

    <hr>

    {{-- 2) 修改密碼 --}}
    <section class="space-y-4">
        <h2 class="text-lg font-medium">變更密碼</h2>
        <form id="form-password" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">目前密碼（current_password）</label>
                    <input name="current_password" type="password" class="w-full rounded-md border px-3 py-2" />
                </div>
                <div></div>
                <div>
                    <label class="block text-sm text-gray-600">新密碼（password）</label>
                    <input name="password" type="password" class="w-full rounded-md border px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600">確認新密碼（password_confirmation）</label>
                    <input name="password_confirmation" type="password" class="w-full rounded-md border px-3 py-2" />
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" class="rounded-md bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                    更新密碼
                </button>
            </div>
        </form>
    </section>

    <hr>

    {{-- 3) 刪除帳號 --}}
    <section class="space-y-4">
        <h2 class="text-lg font-medium text-red-600">刪除帳號</h2>
        <p class="text-sm text-gray-600">此動作無法回復，請謹慎操作。</p>
        <form id="form-delete" class="space-y-3">
            <div>
                <label class="block text-sm text-gray-600">請輸入密碼以確認（password）</label>
                <input name="password" type="password" class="w-full rounded-md border px-3 py-2" />
            </div>
            <button type="submit" class="rounded-md bg-red-600 text-white px-4 py-2 hover:bg-red-700">
                永久刪除帳號
            </button>
        </form>
    </section>
</div>

{{-- ====== API 綁定腳本 ====== --}}
<script>
const API_CONFIG = {
    BASE_URL: '{{ env("API_BASE_URL", "http://120.110.115.126:18081") }}',
    PATHS: {
        getProfile:      '/user/info',
        updateProfile:   '/user/update_profile',  // 使用Laravel路由
        updatePassword:  '/user/update_pwd',       // 使用Laravel路由
        deleteUser:      '/user/delete'
    },
    // 允許三種來源，依你實際情況擇一即可
    get token() {
        return @json(session('auth_token')) || localStorage.getItem('access_token') || '';
    }
};

function assertToken() {
    if (!API_CONFIG.token) {
        console.warn('No token found. Redirecting to login …');
        alert('尚未登入或登入已過期，請重新登入。');
        // TODO: 若有你的登入頁，導過去
        // window.location.href = '/login';
        throw new Error('MISSING_TOKEN');
    }
}

function headers(isJson = true) {
    const h = {};
    if (isJson) h['Content-Type'] = 'application/json';
    h['Accept'] = '*/*';
    const t = API_CONFIG.token;
    if (t) h['Authorization'] = 'Bearer ' + t;
    return h;
}

function flash(msg, type='success') {
    const el = document.getElementById('flash');
    el.className = 'rounded-md p-3 text-sm border ' + (type === 'success'
        ? 'bg-green-50 border-green-200 text-green-700'
        : 'bg-red-50 border-red-200 text-red-700');
    el.textContent = msg;
    setTimeout(()=>{ el.classList.add('hidden'); }, 4000);
}

async function safeFetch(url, opts) {
    assertToken();
    const res = await fetch(url, opts);
    // 統一處理 401
    if (res.status === 401) {
        flash('未授權：請重新登入（401）', 'error');
        // 依需求：清掉 token 並導到登入頁
        // localStorage.removeItem('access_token');
        // window.location.href = '/login';
        throw new Error('HTTP_401');
    }
    return res;
}

async function loadProfile() {
    try {
        const res = await safeFetch(API_CONFIG.BASE_URL + API_CONFIG.PATHS.getProfile, {
            method: 'GET',
            headers: headers(false),
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.message || '載入失敗');

        const d = json.data || {};
        const f = document.getElementById('form-profile');
        ['account','name','nick_name','birthday','email','phone'].forEach(k=>{
            if (f.elements[k] !== undefined && d[k] !== undefined) f.elements[k].value = d[k] ?? '';
        });
        const avatarBox = document.getElementById('avatarBox');
        if (Array.isArray(d.user_img_responses) && d.user_img_responses.length) {
            const url = d.user_img_responses[0].url;
            avatarBox.innerHTML = `<span class="mr-2">大頭貼：</span>
                <a class="text-indigo-600 underline" href="${url}" target="_blank" rel="noopener">${url}</a>`;
        } else {
            avatarBox.textContent = '尚未設定大頭貼';
        }
    } catch (e) {
        if (e.message !== 'HTTP_401' && e.message !== 'MISSING_TOKEN') {
            console.error(e);
            flash('無法載入個人資料：' + e.message, 'error');
        }
    }
}

async function submitProfile(e) {
    e.preventDefault();
    const f = e.target;
    const payload = {
        name: f.name.value?.trim(),
        email: f.email.value?.trim(),
        phone: f.phone.value?.trim() || undefined,
        file_id: f.file_id?.value ? Number(f.file_id.value) : 0
    };
    try {
        // 使用Laravel路由，不需要BASE_URL
        const res = await fetch(API_CONFIG.PATHS.updateProfile, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
        const json = await res.json().catch(()=>({}));
        if (!res.ok || json.success === false) {
            throw new Error(json.message || ('更新失敗，HTTP ' + res.status));
        }
        flash('基本資料已更新！', 'success');
        loadProfile();
    } catch (e) {
        console.error(e);
        flash('更新失敗：' + e.message, 'error');
    }
}

async function submitPassword(e) {
    e.preventDefault();
    const f = e.target;
    const payload = {
        current_password: f.current_password.value,
        password: f.password.value,
        password_confirmation: f.password_confirmation.value
    };
    try {
        const res = await safeFetch(API_CONFIG.BASE_URL + API_CONFIG.PATHS.updatePassword, {
            method: 'PUT',
            headers: headers(true),
            body: JSON.stringify(payload)
        });
        const json = await res.json().catch(()=>({}));
        if (!res.ok || json.success === false) {
            throw new Error(json.message || ('更新密碼失敗，HTTP ' + res.status));
        }
        f.reset();
        flash('密碼已更新！', 'success');
    } catch (e) {
        if (e.message !== 'HTTP_401' && e.message !== 'MISSING_TOKEN') {
            console.error(e);
            flash('更新密碼失敗：' + e.message, 'error');
        }
    }
}

async function submitDelete(e) {
    e.preventDefault();
    if (!confirm('確定要永久刪除帳號嗎？此動作無法回復。')) return;
    const f = e.target;
    const payload = { password: f.password.value };
    try {
        const res = await safeFetch(API_CONFIG.BASE_URL + API_CONFIG.PATHS.deleteUser, {
            method: 'DELETE',
            headers: headers(true),
            body: JSON.stringify(payload)
        });
        const json = await res.json().catch(()=>({}));
        if (!res.ok || json.success === false) {
            throw new Error(json.message || ('刪除失敗，HTTP ' + res.status));
        }
        flash('帳號已刪除。', 'success');
        // window.location.href = '/login';
    } catch (e) {
        if (e.message !== 'HTTP_401' && e.message !== 'MISSING_TOKEN') {
            console.error(e);
            flash('刪除失敗：' + e.message, 'error');
        }
    }
}

document.getElementById('form-profile').addEventListener('submit', submitProfile);
document.getElementById('form-password').addEventListener('submit', submitPassword);
document.getElementById('form-delete').addEventListener('submit', submitDelete);

loadProfile();
</script>

@endsection
