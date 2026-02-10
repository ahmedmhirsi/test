Write-Host "--------------------------------------------------------" -ForegroundColor Cyan
Write-Host " Ngrok Setup" -ForegroundColor Cyan
Write-Host "--------------------------------------------------------" -ForegroundColor Cyan
Write-Host "1. Go to: https://dashboard.ngrok.com/get-started/your-authtoken" -ForegroundColor White
Write-Host "   (Sign up for free if you don't have an account)" -ForegroundColor White
Write-Host "2. Copy the token that starts with 2..." -ForegroundColor White
Write-Host ""
$token = Read-Host "3. Right-click to Paste your Authtoken here and press Enter"

if (-not (Test-Path "ngrok.exe")) {
    Write-Host "Downloading ngrok..."
    $url = "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip"
    Invoke-WebRequest -Uri $url -OutFile "ngrok.zip"
    Expand-Archive -Path "ngrok.zip" -DestinationPath . -Force
}

./ngrok config add-authtoken $token
Write-Host "Starting Tunnel..." -ForegroundColor Green
./ngrok http 8000
