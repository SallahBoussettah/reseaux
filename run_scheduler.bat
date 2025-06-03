@echo off
echo Running Laravel Scheduler...
cd /d %~dp0
:loop
php artisan schedule:run
timeout /t 60
goto loop 