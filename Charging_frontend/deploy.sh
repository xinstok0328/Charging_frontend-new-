#!/usr/bin/env bash
set -euo pipefail

# ===== 可調參數 =====
APP_NAME=${APP_NAME:-charging}
OUT_DIR=${OUT_DIR:-./_release}
NEED_COMPOSER=${NEED_COMPOSER:-true}   # 若 vendor 不存在或想重整，設 true
NEED_VITE_BUILD=${NEED_VITE_BUILD:-false} # 若你要此腳本順便跑 npm build，設 true

# ===== 前置檢查 =====
[ -f composer.json ] || { echo "找不到 composer.json，請在 Laravel 專案根目錄執行"; exit 1; }
[ -f .env ] || { echo "找不到 .env，請先準備好環境檔"; exit 1; }

# =====（可選）建置 =====
if [ "${NEED_VITE_BUILD}" = "true" ]; then
  if command -v npm >/dev/null 2>&1; then
    echo ">> npm ci && npm run build"
    npm ci
    npm run build            # 會輸出到 public/build
  else
    echo "⚠️  找不到 npm，略過前端 build"
  fi
fi

if [ "${NEED_COMPOSER}" = "true" ]; then
  if command -v composer >/dev/null 2>&1; then
    echo ">> composer install --no-dev --optimize-autoloader"
    composer install --no-dev --optimize-autoloader
  else
    echo "⚠️  找不到 composer，略過後端依賴安裝"
  fi
fi

# ===== 產物命名 =====
STAMP=$(date +%Y%m%d-%H%M%S)
PKG_NAME="${APP_NAME}-${STAMP}.tar.gz"
STAGE="${OUT_DIR}/${APP_NAME}-${STAMP}"

# ===== 準備暫存資料夾 =====
echo ">> 準備打包暫存：${STAGE}"
rm -rf "${STAGE}" && mkdir -p "${STAGE}"

# ===== 複製必要檔案（只要你列的那些）=====
copy_paths=(
  "app"
  "bootstrap"
  "config"
  "database"
  "public"
  "resources"
  "routes"
  "storage"
  "vendor"
  "artisan"
  "composer.json"
  "composer.lock"
  ".env"
)

for p in "${copy_paths[@]}"; do
  if [ -e "$p" ]; then
    rsync -a "$p" "${STAGE}/"
  fi
done

# 清理不必要內容（縮小包體）
rm -rf "${STAGE}/node_modules" || true
rm -rf "${STAGE}/storage/logs/"* || true
rm -rf "${STAGE}/.git" || true

# 確認前端打包結果存在
if [ ! -f "${STAGE}/public/build/manifest.json" ]; then
  echo "⚠️  未找到 public/build/manifest.json，請先跑 npm run build（或把 NEED_VITE_BUILD=true）"
fi

# ===== 打包 =====
mkdir -p "${OUT_DIR}"
tar -C "${OUT_DIR}" -czf "${OUT_DIR}/${PKG_NAME}" "$(basename "${STAGE}")"

echo "✅ 打包完成：${OUT_DIR}/${PKG_NAME}"
echo "   內容根目錄：$(basename "${STAGE}")"
