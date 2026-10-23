@echo off
chcp 65001 >nul
title Website Dat Mon & Cloudflare Tunnel

echo ========================================================
echo   DANG KHOI DONG HE THONG WEB VA DUONG LINK CONG KHAI
echo ========================================================
echo.

:: 1. Kiem tra xem PHP Server co dang chay khong
netstat -ano | findstr :8000 >nul
if %errorlevel% equ 0 (
    echo [OK] May chu PHP dang chay tren port 8000.
) else (
    echo [*] Dang bat may chu PHP tren port 8000...
    start "PHP Server (Port 8000)" /min php -S 127.0.0.1:8000 -t public
    timeout /t 2 /nobreak >nul
    echo [OK] Da bat may chu PHP thanh cong.
)

echo.
echo [*] Dang tao duong link cong khai qua Cloudflare Tunnel...
echo.
echo ========================================================
echo   HAY COPY DUONG LINK BAT DAU BANG: https://...trycloudflare.com
echo   (Nhan Ctrl + C de dung chia se link khi khong dung nua)
echo ========================================================
echo.

:: 2. Chay Cloudflare Tunnel
"%~dp0cloudflared.exe" tunnel --url http://127.0.0.1:8000
