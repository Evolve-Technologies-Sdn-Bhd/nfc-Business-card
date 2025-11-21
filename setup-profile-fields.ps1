# Profile Builder Fields 自动设置脚本

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Profile Builder Fields 后端设置" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 进入 backend 目录
Set-Location -Path "backend"

Write-Host "1. 运行数据库迁移..." -ForegroundColor Yellow
php artisan migrate

Write-Host ""
Write-Host "2. 清除缓存..." -ForegroundColor Yellow
php artisan route:clear
php artisan config:clear
php artisan cache:clear

Write-Host ""
Write-Host "3. 重新生成 autoload..." -ForegroundColor Yellow
composer dump-autoload

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "✅ 设置完成！" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "现在你可以：" -ForegroundColor Cyan
Write-Host "1. 刷新前端页面" -ForegroundColor White
Write-Host "2. 访问 /AdminManagement/profile-builder-design" -ForegroundColor White
Write-Host "3. 点击任意 Field Tabs 测试功能" -ForegroundColor White
Write-Host ""
Write-Host "如果服务器正在运行，请重启它：" -ForegroundColor Yellow
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host ""

# 返回项目根目录
Set-Location -Path ".."
