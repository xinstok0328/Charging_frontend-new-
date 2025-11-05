import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// 後端 API 主機
const BASE_URL = 'http://120.110.115.126:18081';

document.addEventListener('DOMContentLoaded', () => {
  // 取得頁面元件
  const form        = document.querySelector('form[action*="register"]');
  const emailInput  = document.getElementById('email');
  const codeInput   = document.getElementById('verifyCode');     // 驗證碼輸入框
  const getCodeBtn  = document.getElementById('getCodeBtn');     // 取得驗證碼按鈕
  const checkBtn    = document.getElementById('checkCodeBtn');   // 確認驗證碼按鈕
  const okFlag      = document.getElementById('emailCodeOK');    // 隱藏欄位，通過=1
  const msgBox      = document.getElementById('msg');            // 顯示訊息的區塊

  // 不是註冊頁就略過（避免其它頁報錯）
  if (!form || !emailInput || !msgBox) return;
  
  // 檢查是否已經有內聯腳本處理驗證碼功能（避免重複綁定事件）
  if (getCodeBtn && getCodeBtn.onclick !== null) return;

  // 工具：Email 格式檢查 + 提示訊息 + 倒數計時
  const isEmail = (s) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);

  const showMsg = (text, ok = true) => {
    msgBox.textContent  = text || '';
    msgBox.style.color  = ok ? '#2e7d32' : '#c62828';  // 綠/紅
  };

  const startCountdown = (btn, sec = 60) => {
    let left = sec;
    const orig = btn.textContent;
    btn.disabled = true;
    const t = setInterval(() => {
      btn.textContent = `${left}s 後可再發送`;
      left--;
      if (left < 0) {
        clearInterval(t);
        btn.disabled = false;
        btn.textContent = orig;
      }
    }, 1000);
  };

  // 寄送驗證碼
  async function sendMailCode() {
    const email = (emailInput.value || '').trim();
    if (!email)  return showMsg('請先輸入 Email', false);
    if (!isEmail(email)) return showMsg('Email 格式不正確', false);

    try {
      showMsg('正在發送驗證碼…');
      getCodeBtn.disabled = true;
      getCodeBtn.textContent = '發送中…';

      const url = `${BASE_URL}/auth/send_mail_code?loginMail=${encodeURIComponent(email)}`;
      const res = await fetch(url, { method: 'GET', headers: { Accept: '*/*' } });
      const data = await res.json().catch(() => ({}));

      if (res.ok && (data?.success === true || data?.code === 20000)) {
        showMsg(data?.message || '驗證碼已寄出，請到信箱查收！', true);
        startCountdown(getCodeBtn, 60);
      } else {
        showMsg(data?.message || `發送失敗（HTTP ${res.status}）`, false);
        getCodeBtn.disabled = false;
        getCodeBtn.textContent = '取得驗證碼';
      }
    } catch (err) {
      showMsg(`發送失敗：${err?.message || err}`, false);
      getCodeBtn.disabled = false;
      getCodeBtn.textContent = '取得驗證碼';
    }
  }

  // 確認驗證碼
  async function checkMailCode() {
    const email = (emailInput.value || '').trim();
    const code  = (codeInput?.value || '').trim();

    if (!email || !code) return showMsg('請輸入 Email 與驗證碼', false);

    try {
      // Swagger 顯示：POST 並用 query 參數
      const url = `${BASE_URL}/auth/check_mail_code?loginMail=${encodeURIComponent(email)}&verifyCode=${encodeURIComponent(code)}`;
      const res = await fetch(url, { method: 'POST', headers: { Accept: '*/*' } });
      const data = await res.json().catch(() => ({}));

      if (res.ok && (data?.success === true || data?.code === 20000)) {
        showMsg('✅ 驗證成功，Email 已通過驗證！', true);
        if (okFlag) okFlag.value = '1';
        // 鎖定欄位/按鈕避免再改
        emailInput.readOnly = true;
        codeInput.readOnly  = true;
        checkBtn && (checkBtn.disabled = true);
      } else {
        showMsg(data?.message || `驗證失敗（HTTP ${res.status}）`, false);
        if (okFlag) okFlag.value = '0';
      }
    } catch (err) {
      showMsg(`驗證失敗：${err?.message || err}`, false);
      if (okFlag) okFlag.value = '0';
    }
  }

  // 綁定事件
  getCodeBtn?.addEventListener('click', sendMailCode);
  checkBtn?.addEventListener('click', checkMailCode);

  // 送出註冊前做前端保護：沒通過驗證碼就阻擋
  form.addEventListener('submit', (e) => {
    if (okFlag && okFlag.value !== '1') {
      e.preventDefault();
      showMsg('請先通過 Email 驗證碼檢查再註冊', false);
    }
  });
});
