@echo off
title JEJ-STREETWEAR - Servidor Publico
chcp 65001 >nul
cls

echo ========================================================
echo        JEJ-STREETWEAR - INICIADOR DE SERVIDOR PUBLICO
echo ========================================================
echo.

:: Verificar si MySQL de XAMPP está activo
tasklist /fi "imagename eq mysqld.exe" | find /i "mysqld.exe" >nul
if errorlevel 1 (
    echo [ADVERTENCIA] El servicio MySQL de XAMPP no parece estar corriendo.
    echo Asegúrate de abrir XAMPP Control Panel y presionar 'Start' en MySQL.
    echo.
)

:: Detener posibles procesos previos en el puerto 8085
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8085') do (
    taskkill /f /pid %%a >nul 2>&1
)

echo [1/2] Iniciando servidor web PHP local...
start /b "" "C:\xampp\php\php.exe" -S 127.0.0.1:8085 -t "%~dp0" >nul 2>&1

echo [2/2] Creando enlace público con Cloudflare Tunnel...
echo.
echo Presiona Ctrl + C en esta ventana cuando quieras apagar el servidor.
echo ========================================================
echo.

"%~dp0scripts\cloudflared.exe" tunnel --url http://127.0.0.1:8085

pause

