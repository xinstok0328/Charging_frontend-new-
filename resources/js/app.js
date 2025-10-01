import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const BASE_URL = 'http://120.110.115.126:18081'; // TODO: 換成註冊用寄碼 API 的主機

document.addEventListener('DOMContentLoaded', () => {
  const emailInput = document.getElementById('email');
  const getCodeBtn = document.getElementById('getCodeBtn');
  const msgBox = document.getElementById('msg');

  if (!emailInput || !getCodeBtn || !msgBox) return; // 不是註冊頁就略過

  const isEmail = (s) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);

  const showMsg = (text, ok = true) => {
    msgBox.textContent = text ?? '';
    msgBox.style.color = ok ? '#2e7d32' : '#c62828';
  };

  const startCountdown = (sec = 60) => {
    let left = sec;
    const orig = getCodeBtn.textContent;
    getCodeBtn.disabled = true;
    const t = setInterval(() => {
      getCodeBtn.textContent = `${left}s 後可再發送`;
      left--;
      if (left < 0) {
        clearInterval(t);
        getCodeBtn.disabled = false;
        getCodeBtn.textContent = orig;
      }
    }, 1000);
  };

  getCodeBtn.addEventListener('click', async () => {
    const email = (emailInput.value || '').trim();
    if (!email) return showMsg('請先輸入 Email', false);
    if (!isEmail(email)) return showMsg('Email 格式不正確', false);

    try {
      showMsg('正在發送驗證碼…');
      getCodeBtn.disabled = true;
      getCodeBtn.textContent = '發送中…';

      // ⚠ 若有註冊專用 API，請把這行改成那一支
      const url = `${BASE_URL}/auth/check_mail_code?loginMail=${encodeURIComponent(email)}`;
      const res = await fetch(url, { method: 'GET', headers: { Accept: '*/*' } });
      const data = await res.json().catch(() => ({}));

      if (res.ok && (data?.success === true || data?.code === 0)) {
        showMsg(data?.message || '驗證碼已寄出，請到信箱查收！', true);
        startCountdown(60);
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
  });
});
