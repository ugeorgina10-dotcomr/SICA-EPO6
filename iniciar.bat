@echo off
cd /d "%~dp0"
title SICA-EPO6 (no cierres esta ventana)
set "PHP=php"
where php >nul 2>nul || set "PHP=C:\xampp\php\php.exe"
start "" http://127.0.0.1:8000
"%PHP%" artisan serve --host=127.0.0.1 --port=8000
pause
